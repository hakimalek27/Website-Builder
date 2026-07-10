<?php

namespace App\Services\Ai;

use App\Models\Project;
use App\Support\Moods;
use App\Support\PiiScrubber;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * §8.3 — bina prompt. System DISALIN VERBATIM dari resources/prompts/draft-system.txt.
 * User = DATA (PII-MINIMIZED §12.7) + SKEMA OUTPUT (§8.2) + NOTA & CITARASA PIC + TWEAK.
 *
 * Fasa 12 W6: DATA diperkaya (sejarah/visi-misi/perutusan/khidmat penuh/kelas/kuliah/
 * FAQ/seed/info-pelawat) supaya draf mencerminkan apa yang PIC isi. PII-min kekal:
 * TIADA telefon/emel individu, no. akaun bank, nama+telefon PIC, IC, no. pendaftaran.
 */
class PromptBuilder
{
    private const SERVICE_PAGES = ['nikah', 'jenazah', 'tahlil_doa', 'khidmat_nasihat', 'sewa_dewan'];

    /**
     * @param  array<string, mixed>|null  $tweak  ['categories'=>[], 'message'=>string, 'current_json'=>array]
     * @return array{system:string, user:string, requested_keys:array<int,string>, service_keys:array<int,string>}
     */
    public function build(Project $project, string $type = 'initial', ?array $tweak = null): array
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();
        $enabledPages = $project->pages()->where('enabled', true)->pluck('page_key')->all();

        $mood = data_get($sections, 'step_2.mood', 'tenang_khusyuk');
        $isNgo = $project->tier->isNgo();

        $system = $this->systemPrompt($mood, $isNgo);

        $data = $this->minimizedData($project, $sections, $enabledPages);
        [$requested, $serviceKeys] = $this->requestedKeys($enabledPages, $sections, $isNgo);
        $schema = $this->schemaFor($requested, $serviceKeys);

        $user = "DATA ORGANISASI:\n".json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ."\n\nSKEMA OUTPUT (balas JSON SAHAJA, tepat mengikut kunci & had panjang):\n"
            .json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Nama khas & singkatan rasmi mesti kekal huruf demi huruf (elak AI 'membetulkan' cth PERKIB→PERKIP).
        $user .= "\n\nEJAAN NAMA (WAJIB): Kekalkan setiap nama khas dan singkatan rasmi TEPAT seperti dalam DATA di atas — huruf demi huruf. JANGAN cipta, pendekkan, atau ubah singkatan pertubuhan.";

        // Nota & citarasa PIC (arahan gaya — bukan fakta skema; PII di-scrub).
        $user .= $this->notesBlock($project, $sections);

        if ($type === 'content_tweak' && $tweak !== null) {
            $user .= "\n\nARAHAN TWEAK:\n"
                ."JSON semasa:\n".json_encode($tweak['current_json'] ?? [], JSON_UNESCAPED_UNICODE)."\n"
                .'Kategori: '.implode(', ', $tweak['categories'] ?? [])."\n"
                .'Arahan PIC: '.($tweak['message'] ?? '')."\n"
                .'Ubah HANYA bahagian berkaitan, kekalkan yang lain.';
        }

        return [
            'system' => $system,
            'user' => $user,
            'requested_keys' => $requested,
            'service_keys' => $serviceKeys,
        ];
    }

    public function systemPrompt(string $mood, bool $isNgo = false): string
    {
        $file = $isNgo ? 'prompts/draft-system-ngo.txt' : 'prompts/draft-system.txt';
        $template = File::get(resource_path($file));

        return str_replace('{{MOOD}}', Moods::prompt($mood), $template);
    }

    /**
     * DATA diminimumkan PII (§12.7): TIADA telefon/emel individu, no. akaun bank penuh,
     * nama+telefon PIC, IC, no. pendaftaran. Bandar/negeri sahaja (bukan alamat/GPS penuh).
     */
    private function minimizedData(Project $project, array $sections, array $enabledPages): array
    {
        $l1 = $sections['step_1'] ?? [];
        $l4 = $sections['step_4']['panels'] ?? [];

        if ($project->tier->isNgo()) {
            return $this->minimizedNgoData($project, $sections, $l1, $l4);
        }

        $data = [
            'nama_masjid' => $project->mosque_name,
            'nama_pendek' => $l1['short_name'] ?? null,
            'bandar' => $l1['city'] ?? null,
            'negeri' => $l1['state'] ?? null,
            'tahun_ditubuhkan' => $l1['established_year'] ?? null,
            'kapasiti' => $l1['capacity'] ?? null,
            'pihak_berkuasa' => $l1['authority'] ?? null,
            'mood' => data_get($sections, 'step_2.mood'),
            'tier' => $project->tier->value,
            'sejarah' => $this->sejarahData($l4['sejarah'] ?? []),
            'visi_misi' => $this->visiMisi($l4['visi_misi'] ?? []),
            'perutusan' => $this->perutusanData($l4['perutusan'] ?? []),
            'soalan_lazim' => $this->faqData($l4['soalan_lazim'] ?? []),
            'berita_seed' => $this->seedItems($l4['berita'] ?? []),
            'pengumuman_seed' => $this->seedItems($l4['pengumuman'] ?? []),
            'info_pelawat' => $this->visitorInfoData($l4['info_pelawat'] ?? []),
            'khairat' => $this->khairatData($l4['khairat'] ?? []),
            'hero_mode' => data_get($sections, 'step_6.hero_mode'),
            'halaman_custom' => $this->customPagesData($sections['step_3'] ?? []),
        ];

        // Khidmat (diperkaya: + penerangan/syarat/cara mohon; TANPA contact/dokumen).
        $services = [];
        foreach (self::SERVICE_PAGES as $page) {
            if (in_array($page, $enabledPages, true) && ! empty($l4[$page]['short_desc'] ?? null)) {
                $services[] = $this->serviceData($page, $l4[$page]);
            }
        }
        if ($services !== []) {
            $data['khidmat'] = $services;
        }

        // Fasiliti (label sahaja).
        if (! empty($l4['fasiliti']['items'] ?? [])) {
            $data['fasiliti'] = array_values($l4['fasiliti']['items']);
        }

        // Kelas Quran (butiran penuh).
        if (! empty($l4['kelas_quran']['classes'] ?? [])) {
            $data['kelas_quran'] = array_map(fn ($c) => array_filter([
                'nama' => $c['name'] ?? null, 'peringkat' => $c['level'] ?? null,
                'hari' => $c['days'] ?? null, 'masa' => $c['time'] ?? null,
                'lokasi' => $c['location'] ?? null, 'fokus' => $c['focus'] ?? null, 'yuran' => $c['fee'] ?? null,
            ], fn ($v) => filled($v)), $l4['kelas_quran']['classes']);
        }

        // Kuliah (tajuk/hari/masa/penceramah/kitab).
        if (! empty($l4['kuliah_mingguan']['sessions'] ?? [])) {
            $data['kuliah'] = array_map(fn ($s) => array_filter([
                'tajuk' => $s['topic'] ?? null, 'hari' => $s['day'] ?? null, 'masa' => $s['time'] ?? null,
                'penceramah' => $s['speaker'] ?? null, 'kitab' => $s['kitab'] ?? null,
            ], fn ($v) => filled($v)), $l4['kuliah_mingguan']['sessions']);
        }

        // Infaq: TAJUK + desc kategori sahaja (TIADA no akaun bank).
        if (! empty($l4['infaq']['categories'] ?? [])) {
            $data['infaq_kategori'] = $this->categoryTitles($l4['infaq']['categories']);
        }

        return array_filter($data, fn ($v) => $v !== null && $v !== []);
    }

    /**
     * DATA NGO diminimumkan PII (§12.7): TIADA no. pendaftaran, no. akaun bank,
     * nama+telefon individu/PIC. Program/sukarelawan/keahlian/derma + konteks am.
     */
    private function minimizedNgoData(Project $project, array $sections, array $l1, array $l4): array
    {
        $data = [
            'nama_pertubuhan' => $project->mosque_name,
            'nama_pendek' => $l1['short_name'] ?? null,
            'bandar' => $l1['city'] ?? null,
            'negeri' => $l1['state'] ?? null,
            'tahun_ditubuhkan' => $l1['established_year'] ?? null,
            'mood' => data_get($sections, 'step_2.mood'),
            'tier' => $project->tier->value,
            'profil' => $this->sejarahData($l4['profil'] ?? []),
            'visi_misi' => $this->visiMisi($l4['visi_misi'] ?? []),
            'perutusan' => $this->perutusanData($l4['perutusan'] ?? []),
            'soalan_lazim' => $this->faqData($l4['soalan_lazim'] ?? []),
            'berita_seed' => $this->seedItems($l4['berita'] ?? []),
            'pengumuman_seed' => $this->seedItems($l4['pengumuman'] ?? []),
            'acara_akan_datang' => $this->seedItems($l4['program_akan_datang'] ?? []),
            'hero_mode' => data_get($sections, 'step_6.hero_mode'),
            'halaman_custom' => $this->customPagesData($sections['step_3'] ?? []),
        ];

        // Program (nama + sasaran + penerangan + jadual).
        if (! empty($l4['program_utama']['programs'] ?? [])) {
            $data['program'] = array_map(fn ($p) => array_filter([
                'nama' => $p['name'] ?? null, 'sasaran' => $p['audience'] ?? null,
                'penerangan' => filled($p['desc'] ?? null) ? Str::limit((string) $p['desc'], 300) : null,
                'jadual' => $p['schedule'] ?? null,
            ], fn ($v) => filled($v)), $l4['program_utama']['programs']);
        }

        // Sukarelawan (pengenalan + bidang + komitmen; TANPA form_url/contact).
        if (filled($l4['sukarelawan']['intro'] ?? null) || ! empty($l4['sukarelawan']['roles'] ?? [])) {
            $data['sukarelawan'] = array_filter([
                'pengenalan' => filled($l4['sukarelawan']['intro'] ?? null) ? Str::limit((string) $l4['sukarelawan']['intro'], 600) : null,
                'bidang' => ! empty($l4['sukarelawan']['roles'] ?? []) ? array_values(array_filter(array_map(
                    fn ($r) => array_filter(['bidang' => $r['bidang'] ?? null, 'komitmen' => $r['komitmen'] ?? null], fn ($v) => filled($v)),
                    $l4['sukarelawan']['roles'],
                ))) : null,
            ], fn ($v) => filled($v));
        }

        // Keahlian (syarat + yuran + manfaat).
        if (filled($l4['keahlian']['criteria'] ?? null) || filled($l4['keahlian']['fee'] ?? null) || ! empty($l4['keahlian']['benefits'] ?? [])) {
            $data['keahlian'] = array_filter([
                'syarat' => filled($l4['keahlian']['criteria'] ?? null) ? Str::limit((string) $l4['keahlian']['criteria'], 500) : null,
                'yuran' => $l4['keahlian']['fee'] ?? null,
                'manfaat' => ! empty($l4['keahlian']['benefits'] ?? []) ? array_values($l4['keahlian']['benefits']) : null,
            ], fn ($v) => filled($v));
        }

        // Derma: TAJUK + desc kategori sahaja (TIADA no akaun bank).
        if (! empty($l4['derma']['categories'] ?? [])) {
            $data['derma_kategori'] = $this->categoryTitles($l4['derma']['categories']);
        }

        return array_filter($data, fn ($v) => $v !== null && $v !== []);
    }

    // --- Pembantu pengekstrakan (PII-min) ---

    /** @param array<string,mixed> $p */
    private function sejarahData(array $p): ?array
    {
        $out = [];
        if (($p['mode'] ?? null) === 'tulis_penuh' && filled($p['full_text'] ?? null)) {
            $out['teks'] = Str::limit((string) $p['full_text'], 1500);
        } elseif (! empty($p['bullets'] ?? [])) {
            $out['butir'] = array_values($p['bullets']);
        }
        if (! empty($p['milestones'] ?? [])) {
            $out['peristiwa'] = array_values(array_filter(array_map(
                fn ($m) => array_filter(['tahun' => $m['tahun'] ?? null, 'peristiwa' => $m['peristiwa'] ?? null], fn ($v) => filled($v)),
                $p['milestones'],
            )));
        }

        return $out ?: null;
    }

    /** @param array<string,mixed> $p */
    private function visiMisi(array $p): ?array
    {
        $out = array_filter([
            'visi' => $p['visi'] ?? null,
            'misi' => $p['misi'] ?? null,
            'moto' => $p['moto'] ?? null,
        ], fn ($v) => filled($v));

        return $out ?: null;
    }

    /** Jawatan + teks perutusan. NAMA TIDAK dihantar ke AI (shell render verbatim; elak salah eja). */
    private function perutusanData(array $p): ?array
    {
        if (blank($p['message'] ?? null)) {
            return null;
        }

        return array_filter([
            'jawatan' => $p['role'] ?? null,
            'teks' => Str::limit((string) $p['message'], 800),
        ], fn ($v) => filled($v));
    }

    /** @param array<string,mixed> $p */
    private function faqData(array $p): ?array
    {
        $out = [];
        foreach (array_slice($p['faqs'] ?? [], 0, 8) as $f) {
            if (blank($f['q'] ?? null)) {
                continue;
            }
            $out[] = array_filter([
                'soalan' => $f['q'] ?? null,
                'jawapan' => filled($f['a'] ?? null) ? Str::limit((string) $f['a'], 300) : null,
            ], fn ($v) => filled($v));
        }

        return $out ?: null;
    }

    /** @param array<string,mixed> $p */
    private function seedItems(array $p): ?array
    {
        $out = [];
        foreach ($p['seed_items'] ?? [] as $it) {
            $row = array_filter([
                'tajuk' => $it['tajuk'] ?? null, 'tarikh' => $it['tarikh'] ?? null,
                'ringkasan' => $it['ringkasan'] ?? null, 'lokasi' => $it['lokasi'] ?? null,
            ], fn ($v) => filled($v));
            if ($row !== []) {
                $out[] = $row;
            }
        }

        return $out ?: null;
    }

    /** @param array<string,mixed> $p */
    private function serviceData(string $page, array $p): array
    {
        $out = [
            'key' => $page,
            'ringkasan' => $p['short_desc'] ?? null,
            'fee' => $p['fee'] ?? null,
            'penerangan' => filled($p['full_desc'] ?? null) ? Str::limit((string) $p['full_desc'], 400) : null,
            'syarat' => ! empty($p['requirements'] ?? []) ? array_values($p['requirements']) : null,
            'cara_mohon' => $p['apply_method'] ?? null,
        ];
        if ($page === 'sewa_dewan') {
            $out['kapasiti'] = $p['capacity'] ?? null;
            if (! empty($p['rates'] ?? [])) {
                $out['kadar'] = array_values(array_filter(array_map(
                    fn ($r) => array_filter(['pakej' => $r['pakej'] ?? null, 'harga' => $r['harga'] ?? null], fn ($v) => filled($v)),
                    $p['rates'],
                )));
            }
        }

        return array_filter($out, fn ($v) => $v !== null && $v !== []);
    }

    /** @param array<int,array<string,mixed>> $cats */
    private function categoryTitles(array $cats): array
    {
        $out = [];
        foreach ($cats as $c) {
            if (blank($c['title'] ?? null)) {
                continue;
            }
            $out[] = array_filter(['tajuk' => $c['title'] ?? null, 'desc' => $c['desc'] ?? null], fn ($v) => filled($v));
        }

        return $out;
    }

    /** @param array<string,mixed> $p */
    private function khairatData(array $p): ?array
    {
        $out = array_filter([
            'yuran' => $p['monthly_fee'] ?? null,
            'terma' => filled($p['terms'] ?? null) ? Str::limit((string) $p['terms'], 300) : null,
        ], fn ($v) => filled($v));

        return $out ?: null;
    }

    /** @param array<string,mixed> $p */
    private function visitorInfoData(array $p): ?array
    {
        $out = [];
        if (! empty($p['visiting_hours'] ?? [])) {
            $out['waktu_lawatan'] = array_values(array_filter(array_map(
                fn ($h) => array_filter(['hari' => $h['hari'] ?? null, 'masa' => $h['masa'] ?? null], fn ($v) => filled($v)),
                $p['visiting_hours'],
            )));
        }
        if (filled($p['dress_code'] ?? null)) {
            $out['kod_pakaian'] = $p['dress_code'];
        }
        if (filled($p['getting_here'] ?? null)) {
            $out['cara_ke_sini'] = $p['getting_here'];
        }
        if (! empty($p['tour_available'])) {
            $out['lawatan_berpandu'] = true;
        }
        if (! empty($p['english_khutbah'])) {
            $out['khutbah_inggeris'] = true;
        }

        return $out ?: null;
    }

    /** @param array<string,mixed> $step3 */
    private function customPagesData(array $step3): ?array
    {
        $out = [];
        foreach ($step3['custom'] ?? [] as $c) {
            if (blank($c['name'] ?? null)) {
                continue;
            }
            $out[] = array_filter(['nama' => $c['name'] ?? null, 'tujuan' => $c['purpose'] ?? null], fn ($v) => filled($v));
        }

        return $out ?: null;
    }

    /** Blok NOTA & CITARASA PIC (step-7 rujukan/dislikes + step-9 free_notes + nota PIC terkini). */
    private function notesBlock(Project $project, array $sections): string
    {
        $l7 = $sections['step_7'] ?? [];
        $l9 = $sections['step_9'] ?? [];
        $lines = [];

        foreach ($l7['liked_refs'] ?? [] as $ref) {
            $url = $ref['url'] ?? null;
            $what = $ref['what_liked'] ?? null;
            if (filled($url) || filled($what)) {
                $lines[] = '- Rujukan disukai: '.trim((string) ($url ?? '').(filled($what) ? ' — '.$what : ''));
            }
        }
        if (filled($l7['dislikes'] ?? null)) {
            $lines[] = '- Elakkan: '.$l7['dislikes'];
        }
        if (filled($l9['free_notes'] ?? null)) {
            $lines[] = '- Nota borang PIC: '.PiiScrubber::scrub((string) $l9['free_notes']);
        }
        foreach ($project->notes()->where('author', 'pic')->latest()->take(5)->get() as $note) {
            if (filled($note->body)) {
                $lines[] = '- Nota PIC: '.Str::limit(PiiScrubber::scrub((string) $note->body), 500);
            }
        }

        if ($lines === []) {
            return '';
        }

        return "\n\nNOTA & CITARASA PIC (ambil kira dalam NADA, pemilihan & penekanan kandungan — "
            ."JANGAN salin bulat-bulat, JANGAN jadikan fakta baharu):\n".implode("\n", $lines);
    }

    /** @return array{0: array<int,string>, 1: array<int,string>} */
    private function requestedKeys(array $enabledPages, array $sections, bool $isNgo = false): array
    {
        $l4 = $sections['step_4']['panels'] ?? [];
        $keys = ['meta', 'hero', 'about', 'footer_description'];

        // Bersama (masjid + NGO) — bila halaman aktif & ADA data.
        if (in_array('visi_misi', $enabledPages, true) && $this->visiMisi($l4['visi_misi'] ?? []) !== null) {
            $keys[] = 'visi_misi';
        }
        if (in_array('perutusan', $enabledPages, true) && $this->perutusanData($l4['perutusan'] ?? []) !== null) {
            $keys[] = 'perutusan';
        }
        if (in_array('soalan_lazim', $enabledPages, true) && $this->faqData($l4['soalan_lazim'] ?? []) !== null) {
            $keys[] = 'faq';
        }

        if ($isNgo) {
            if (in_array('program_utama', $enabledPages, true)) {
                $keys[] = 'programs';
            }
            if (in_array('sukarelawan', $enabledPages, true)) {
                $keys[] = 'volunteer';
            }
            if (in_array('keahlian', $enabledPages, true)) {
                $keys[] = 'membership';
            }
            if (in_array('derma', $enabledPages, true)) {
                $keys[] = 'donate';
            }
            if ($this->hasAnnouncementSeed($l4, true)) {
                $keys[] = 'announcements';
            }

            return [$keys, []];
        }

        $serviceKeys = array_values(array_intersect(self::SERVICE_PAGES, $enabledPages));
        if ($serviceKeys !== []) {
            $keys[] = 'services';
        }
        if (in_array('fasiliti', $enabledPages, true)) {
            $keys[] = 'facilities';
        }
        if (in_array('kuliah_mingguan', $enabledPages, true)) {
            $keys[] = 'kuliah';
        }
        if (in_array('infaq', $enabledPages, true)) {
            $keys[] = 'infaq';
        }
        if ($this->hasAnnouncementSeed($l4, false)) {
            $keys[] = 'announcements';
        }
        if (in_array('info_pelawat', $enabledPages, true)) {
            $keys[] = 'visitor_info';
        }

        return [$keys, $serviceKeys];
    }

    /** Announcements diminta HANYA bila ada seed sebenar (elak AI mereka tajuk/tarikh acara). */
    private function hasAnnouncementSeed(array $l4, bool $isNgo): bool
    {
        $pages = $isNgo ? ['berita', 'pengumuman', 'program_akan_datang'] : ['berita', 'pengumuman'];
        foreach ($pages as $p) {
            if (! empty($l4[$p]['seed_items'] ?? [])) {
                return true;
            }
        }

        return false;
    }

    /** Skema §8.2 dengan hanya kunci diminta. */
    private function schemaFor(array $requested, array $serviceKeys): array
    {
        $all = [
            'meta' => ['title' => '≤60', 'description' => '≤160'],
            'hero' => ['eyebrow' => '≤40', 'headline' => '≤60', 'subheadline' => '≤140', 'cta_primary_label' => '≤20', 'cta_secondary_label' => '≤20'],
            'about' => ['heading' => '≤60', 'paragraphs' => ['60–120 patah, 2–3 item'], 'stats' => [['label' => '≤20', 'value' => '≤12']]],
            'visi_misi' => ['visi' => '≤300', 'misi' => '≤400', 'moto' => '≤120'],
            'perutusan' => ['heading' => '≤60', 'quote' => '≤500'],
            'services' => [['key' => 'dari: '.implode('/', $serviceKeys), 'title' => '≤40', 'blurb' => '≤160']],
            'facilities' => [['title' => '≤40', 'blurb' => '≤140']],
            'kuliah' => ['heading' => '≤60', 'intro' => '≤300'],
            'infaq' => ['heading' => '≤60', 'paragraph' => '≤400'],
            'faq' => [['q' => '≤120', 'a' => '≤400']],
            'announcements' => [['title' => '≤70', 'date_label' => '≤20', 'excerpt' => '≤140']],
            'visitor_info' => ['heading' => '≤60', 'paragraph' => '≤400'],
            // NGO / pertubuhan.
            'programs' => [['title' => '≤40', 'blurb' => '≤160']],
            'volunteer' => ['heading' => '≤60', 'paragraph' => '≤600', 'cta_label' => '≤20'],
            'membership' => ['heading' => '≤60', 'paragraph' => '≤600'],
            'donate' => ['heading' => '≤60', 'paragraph' => '≤600'],
            'footer_description' => '≤200',
        ];

        return array_intersect_key($all, array_flip($requested));
    }
}
