<?php

namespace App\Services\Ai;

use App\Models\Project;
use App\Services\DesignResolver;
use App\Support\Moods;
use App\Support\PageCatalog;
use App\Support\PiiScrubber;
use Illuminate\Support\Facades\File;

/**
 * §Fasa 13 — pembina prompt saluran HTML dua-peringkat.
 *
 * Peringkat 1 (engineerRequest): minta penyedia "Jurutera Prompt" (cth gpt-5.5) menyusun
 *   SATU prompt lengkap (teks) untuk menjana draf HTML — bawa SEMUA data organisasi +
 *   spesifikasi reka bentuk + nota citarasa + spesifikasi placeholder.
 * Peringkat 2 (stage2Request / stage2TweakRequest): hantar prompt itu (atau HTML semasa +
 *   arahan tweak) ke penyedia Default (cth glm-5.2) untuk menjana / mengubah HTML.
 *
 * PII-min §12.7 kekal: KONTEKS dari PromptBuilder::minimizedContext() (TIADA bank/telefon/
 * emel/nama-perutusan/IC). Data verbatim dimasukkan kemudian oleh HtmlDraftFinisher melalui
 * token placeholder [[...]] — BUKAN melalui AI.
 */
class HtmlPromptBuilder
{
    public function __construct(
        private PromptBuilder $promptBuilder,
        private DesignResolver $designResolver,
    ) {}

    /** @return array{system:string, user:string} */
    public function engineerRequest(Project $project): array
    {
        $ctx = $this->promptBuilder->minimizedContext($project);

        $user = $this->contextBlocks($project, $ctx)
            ."\n\nHasilkan prompt lengkap itu sekarang (teks biasa, tanpa pagar kod).";

        return [
            'system' => $this->systemFor('prompt-engineer-system.txt', $ctx['mood']),
            'user' => $user,
        ];
    }

    /** @return array{system:string, user:string} */
    public function stage2Request(Project $project, string $engineeredPrompt): array
    {
        return [
            'system' => $this->systemFor('html-draft-system.txt', $this->moodOf($project)),
            'user' => $engineeredPrompt,
        ];
    }

    /**
     * @param  array{categories?: array<int,string>, message?: string}  $tweak
     * @return array{system:string, user:string}
     */
    public function stage2TweakRequest(Project $project, string $currentHtml, array $tweak): array
    {
        $categories = implode(', ', $tweak['categories'] ?? []);
        $message = PiiScrubber::scrub((string) ($tweak['message'] ?? ''));

        $user = "HTML SEMASA (draf sedia ada):\n".$currentHtml
            ."\n\nARAHAN TWEAK PIC:\n"
            .'Kategori: '.$categories."\n"
            .'Arahan: '.$message."\n\n"
            .'Kembalikan HTML PENUH yang telah dikemas kini — ubah HANYA bahagian berkaitan, '
            .'kekalkan reka bentuk keseluruhan, semua token placeholder [[...]] dan seksyen lain.';

        return [
            'system' => $this->systemFor('html-draft-system.txt', $this->moodOf($project)),
            'user' => $user,
        ];
    }

    // --- dalaman ---

    /**
     * @param  array{data: array<string,mixed>, notes: string, mood: string, is_ngo: bool}  $ctx
     */
    private function contextBlocks(Project $project, array $ctx): string
    {
        $out = "KONTEKS ORGANISASI (guna SEMUA fakta ini dalam prompt — jangan reka tambahan):\n"
            .json_encode($ctx['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $out .= "\n\nSPESIFIKASI REKA BENTUK (salin TEPAT ke dalam prompt — kod warna hex, fon, susun atur):\n"
            .json_encode($this->designSpec($project), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $out .= "\n\nHALAMAN DIPILIH (setiap satu WAJIB jadi <section id=\"{page_key}\"> — guna page_key TEPAT sebagai atribut id, jangan terjemah/ubah):\n"
            .$this->pageList($project);

        $out .= "\n\nPLACEHOLDER WAJIB (arahkan model penjana letak token INI TEPAT, JANGAN isi kandungan — pelayan akan ganti):\n"
            .$this->placeholderSpec($project);

        // notes sudah termasuk tajuk "NOTA & CITARASA PIC" (atau string kosong).
        $out .= $ctx['notes'];

        // Ejaan nama — sama seperti PromptBuilder::build().
        $out .= "\n\nEJAAN NAMA (WAJIB): Kekalkan setiap nama khas dan singkatan rasmi TEPAT — huruf demi huruf. "
            .'JANGAN cipta, pendekkan, atau ubah singkatan pertubuhan.';

        return $out;
    }

    /** @return array<string,mixed> */
    private function designSpec(Project $project): array
    {
        $d = $this->designResolver->resolve($project);
        $step2 = $project->sections()->where('section_key', 'step_2')->value('data') ?? [];

        return [
            'warna' => $d['tokens'],
            'fon' => $d['fonts'],
            'susun_atur' => $d['layout'],
            'header' => $d['header'],
            'footer' => $d['footer'],
            'kad' => $d['card'],
            'pembatas' => $d['divider'],
            'gaya_ikon' => $d['icon_style'],
            'animasi' => $d['animations'],
            'nada' => Moods::prompt((string) data_get($step2, 'mood', 'tenang_khusyuk')),
            'elemen_islamik' => array_filter([
                'corak_geometri' => (bool) data_get($step2, 'islamic_elements.corak_geometri', false),
                'pembatas_arabesque' => (bool) data_get($step2, 'islamic_elements.pembatas_arabesque', false),
            ]),
        ];
    }

    private function pageList(Project $project): string
    {
        $meta = PageCatalog::meta();
        $lines = [];
        foreach ($project->pages()->where('enabled', true)->orderBy('sort')->get() as $p) {
            $label = $p->custom_name ?: ($meta[$p->page_key]['label'] ?? $p->page_key);
            $lines[] = '- '.$p->page_key.' — '.$label.' → <section id="'.$p->page_key.'">';
        }

        return $lines === [] ? '- utama — Halaman Utama → <section id="utama">' : implode("\n", $lines);
    }

    /** Placeholder verbatim (bersyarat data/tier) — pelayan (HtmlDraftFinisher) ganti dengan data sebenar. */
    private function placeholderSpec(Project $project): string
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();
        $l4 = $sections['step_4']['panels'] ?? [];
        $enabled = $project->pages()->where('enabled', true)->pluck('page_key')->all();

        $lines = [
            '- [[CONTACT_STRIP]] — jalur maklumat hubungan (telefon/emel/alamat/sosial) sebelum footer. WAJIB.',
        ];

        $hasBank = filled($l4['infaq']['bank_account'] ?? null) || filled($l4['derma']['bank_account'] ?? null);
        if ($hasBank && (in_array('infaq', $enabled, true) || in_array('derma', $enabled, true))) {
            $lines[] = '- [[BANK_BLOCK]] — blok maklumat bank/QR dalam seksyen Infaq/Derma. WAJIB bila seksyen itu ada.';
        }
        if (! empty($l4['ajk']['members'] ?? [])) {
            $lines[] = '- [[AJK_GRID]] — grid ahli jawatankuasa (nama & jawatan) dalam seksyen AJK.';
        }
        if (filled($l4['perutusan']['name'] ?? null)) {
            $lines[] = '- [[PERUTUSAN_NAMA]] — nama & jawatan penyampai perutusan di bawah petikan (tulis jawatan sahaja; nama diganti pelayan).';
        }
        if (data_get($sections, 'step_6.hero_mode') === 'upload') {
            $lines[] = '- [[HERO_IMAGE]] — letak sebagai nilai src bagi <img> hero (pelayan sisip imej sebenar).';
        }
        if ($project->tier->isMosque()) {
            $lines[] = '- [[WAKTU_SOLAT]] — kad waktu solat (pelayan sisip paparan statik berlabel JAKIM e-Solat).';
            $lines[] = '- [[AYAT_ARAB]] — tempat satu ayat Al-Quran (pelayan sisip teks Arab rasmi; JANGAN tulis aksara Arab sendiri).';
        }

        return implode("\n", $lines);
    }

    private function systemFor(string $file, string $mood): string
    {
        $template = File::get(resource_path('prompts/'.$file));

        return str_replace('{{MOOD}}', Moods::prompt($mood), $template);
    }

    private function moodOf(Project $project): string
    {
        return (string) data_get(
            $project->sections()->where('section_key', 'step_2')->value('data'),
            'mood',
            'tenang_khusyuk',
        );
    }
}
