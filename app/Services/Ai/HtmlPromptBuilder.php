<?php

namespace App\Services\Ai;

use App\Models\Project;
use App\Services\DesignResolver;
use App\Services\DraftStyleDirector;
use App\Support\DraftKit;
use App\Support\Moods;
use App\Support\PackageDna;
use App\Support\PageCatalog;
use App\Support\PiiScrubber;
use Illuminate\Support\Facades\File;

/**
 * §Fasa 13 — pembina prompt saluran HTML dua-peringkat.
 * §Fasa 15 — art direction: designSpec kini beri TAKRIFAN + kelas kit (bukan enum kosong);
 *   engineerRequest bawa DNA pakej + arahan keunikan (anti-pendua); stage2Request LAMPIR
 *   cheat-sheet kit + blueprint verbatim (server, bukan via P1 — jimat token & elak rosak).
 *
 * PII-min §12.7 kekal: KONTEKS dari PromptBuilder::minimizedContext() (TIADA bank/telefon/
 * emel/nama-perutusan/IC). Data verbatim disisip HtmlDraftFinisher melalui token [[...]].
 */
class HtmlPromptBuilder
{
    public function __construct(
        private PromptBuilder $promptBuilder,
        private DesignResolver $designResolver,
        private DraftStyleDirector $director,
    ) {}

    /** @return array{system:string, user:string} */
    public function engineerRequest(Project $project): array
    {
        $ctx = $this->promptBuilder->minimizedContext($project);
        $dir = $this->director->directives($project);

        $user = $this->contextBlocks($project, $ctx)
            ."\n\nDNA REKA BENTUK PAKEJ (watak visual — terjemahkan menjadi arahan gaya KONKRIT dalam prompt, bukan salin bulat):\n"
            .PackageDna::for($this->packageKey($project))
            ."\n\n".$dir['keunikan']
            ."\n\nKIT REKA (PENTING): laman akan disuntik Kit CSS premium (kelas rk-*) oleh pelayan. "
            .'Arahkan model penjana untuk MENGGUNA kelas rk-* (cheat-sheet penuh + blueprint seksyen akan dilampir pelayan pada model penjana) dan JANGAN mentakrif semula pemboleh ubah --rk-*. '
            .'Model penjana JANGAN membina sistem CSS dari kosong — guna kelas kit sebagai asas, tambah CSS halus sahaja bila perlu.'
            ."\n\nTAHUN SEMASA: ".now()->year.' — guna untuk hak cipta/tarikh; JANGAN reka tahun lain.'
            ."\n\nHasilkan prompt lengkap itu sekarang (teks biasa, tanpa pagar kod).";

        return [
            'system' => $this->systemFor('prompt-engineer-system.txt', $ctx['mood']),
            'user' => $user,
        ];
    }

    /**
     * §Fasa 15 K1 — lampiran server: cheat-sheet kit + blueprint dipilih Director + tahun.
     * Dibuat di P2 (bukan P1) supaya jurutera prompt tidak menyalin/merosakkan blueprint,
     * dan pada harga input P2 yang murah.
     *
     * @return array{system:string, user:string}
     */
    public function stage2Request(Project $project, string $engineeredPrompt): array
    {
        $append = "\n\n=== KIT REKA (WAJIB GUNA kelas ini — disuntik pelayan) ===\n".DraftKit::cheatSheet()
            ."\n\n=== BLUEPRINT SEKSYEN (contoh STRUKTUR premium — ikut corak & kelas, isi kandungan sebenar) ===\n"
            .$this->director->blueprintBundle($project)
            ."\n\nTAHUN SEMASA: ".now()->year.'. Guna kelas kit rk-* sepenuhnya; JANGAN takrif semula --rk-*.';

        return [
            'system' => $this->systemFor('html-draft-system.txt', $this->moodOf($project)),
            'user' => $engineeredPrompt.$append,
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
            .'kekalkan reka bentuk keseluruhan, semua kelas kit rk-*, semua token placeholder [[...]] dan seksyen lain.';

        return [
            'system' => $this->systemFor('html-draft-system.txt', $this->moodOf($project)),
            'user' => $user,
        ];
    }

    /**
     * §Fasa 15 — permintaan auto-polish (atas HTML MENTAH bertoken, tiada PII). Naik taraf
     * estetik berdasarkan isu QA sambil mengekalkan token, seksyen, dan fakta.
     *
     * @param  array<int,array<string,mixed>>  $issues
     * @param  array<int,array<string,mixed>>  $suggestions
     * @return array{system:string, user:string}
     */
    public function stage2PolishRequest(Project $project, string $rawHtml, array $issues, array $suggestions): array
    {
        $notes = [];
        foreach (array_merge($issues, $suggestions) as $item) {
            $notes[] = '- '.($item['mesej'] ?? ($item['type'] ?? ''));
        }

        $user = "HTML SEMASA (draf bertoken — KEKALKAN setiap token [[...]] dan seksyen):\n".$rawHtml
            ."\n\nNAIK TARAF KUALITI (perbaiki perkara berikut TANPA membuang kandungan/token/seksyen):\n"
            .implode("\n", $notes)
            ."\n\nGunakan kelas Kit REKA (rk-*) sepenuhnya untuk kedalaman, bayang, kontras & ruang. "
            .'Kembalikan HTML PENUH yang dinaik taraf. JANGAN tulis JavaScript atau aksara Arab. '
            ."JANGAN buang token [[...]] atau seksyen sedia ada.\n\n"
            .DraftKit::cheatSheet();

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

        $out .= "\n\nSPESIFIKASI REKA BENTUK (salin TEPAT ke dalam prompt — warna hex, fon, DAN kelas kit yang WAJIB diguna):\n"
            .json_encode($this->designSpec($project), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $out .= "\n\nHALAMAN DIPILIH (setiap satu WAJIB jadi <section id=\"{page_key}\"> — guna page_key TEPAT sebagai atribut id, jangan terjemah/ubah):\n"
            .$this->pageList($project);

        $out .= "\n\nPLACEHOLDER WAJIB (arahkan model penjana letak token INI TEPAT, JANGAN isi kandungan — pelayan akan ganti):\n"
            .$this->placeholderSpec($project);

        $out .= $ctx['notes'];

        $out .= "\n\nEJAAN NAMA (WAJIB): Kekalkan setiap nama khas dan singkatan rasmi TEPAT — huruf demi huruf. "
            .'JANGAN cipta, pendekkan, atau ubah singkatan pertubuhan.';

        return $out;
    }

    /**
     * §Fasa 15 — setiap varian kini {nilai, kelas_kit, nota takrifan} supaya AI tidak
     * mereka-reka maksud kata kunci. radius/headerStyle DITAPIS (vestigial/mengelirukan).
     *
     * @return array<string,mixed>
     */
    private function designSpec(Project $project): array
    {
        $d = $this->designResolver->resolve($project);
        $step2 = $project->sections()->where('section_key', 'step_2')->value('data') ?? [];

        $warna = array_intersect_key($d['tokens'], array_flip(['primary', 'primaryDark', 'accent', 'ink', 'bg', 'bgAlt']));

        $ic = $d['icon_style']['container'] ?? 'bulat-cair';
        $islamik = [];
        if (data_get($step2, 'islamic_elements.corak_geometri', false)) {
            $islamik[] = 'Corak geometri: guna kelas .rk-pattern .rk-pattern--bintang (atau --rub) pada 1-2 seksyen sebagai latar halus.';
        }
        if (data_get($step2, 'islamic_elements.pembatas_arabesque', false)) {
            $islamik[] = 'Pembatas arabesque: guna .rk-divider--arabesque antara seksyen tertentu.';
        }

        return [
            'warna' => $warna,
            'fon' => $d['fonts'],
            'susun_atur' => $this->describe($d['layout'], [
                'hero-tengah' => ['rk-hero--tengah', 'hero berpusat, foto penuh + overlay gelap'],
                'hero-belah' => ['rk-hero--belah', 'teks di kiri, imej berbingkai di kanan'],
                'grid-kad' => ['rk-hero--grid', 'hero ringkas diikuti grid kad sorotan'],
                'klasik-formal' => ['rk-hero--klasik', 'bingkai garis emas atas/bawah, formal'],
                'hero-penuh' => ['rk-hero--penuh', 'latar gradien gelap penuh + corak'],
                'hero-mihrab' => ['rk-hero--mihrab', 'bingkai lengkung mihrab berpusat'],
            ]),
            'header' => $this->describe($d['header'], [
                'padat' => ['rk-header', 'bar lekat kaca (backdrop-blur)'],
                'gradien' => ['rk-header--gradien', 'bar gradien gelap'],
                'tengah' => ['rk-header--tengah', 'jenama & nav berpusat dua baris'],
            ]),
            'footer' => $this->describe($d['footer'], [
                'ringkas' => ['rk-footer--ringkas', 'footer ringkas berpusat'],
                'tengah-jenama' => ['rk-footer--tengah-jenama', 'jenama besar berpusat'],
                'tiga-lajur' => ['rk-footer--tiga-lajur', 'jenama + pautan + hubungi 3 lajur'],
            ]),
            'kad' => $this->describe($d['card'], [
                'lembut' => ['rk-card--lembut', 'border halus + bayang lembut'],
                'garis' => ['rk-card--garis', 'border tegas, TIADA bayang'],
                'terapung' => ['rk-card--terapung', 'tiada border, bayang dalam terapung'],
            ]),
            'pembatas' => $this->describe($d['divider'], [
                'tiada' => ['', 'guna ruang putih & latar berselang sebagai pemisah'],
                'garis-emas' => ['rk-divider--garis-emas', 'garis emas + berlian tengah'],
                'lengkung' => ['rk-divider--lengkung', 'pemisah lengkung lembut'],
            ]),
            'gaya_ikon' => [
                'kelas_bekas' => 'rk-icon--'.$ic,
                'berat_garisan' => $d['icon_style']['weight'] ?? 'sederhana',
                'nota' => 'letak SVG inline dalam <span class="rk-icon rk-icon--'.$ic.'">',
            ],
            'animasi' => match ($d['animations']) {
                'fade' => 'Kelas .rk-anim-fade pada <body>; tanda elemen utama dengan .rk-reveal (fade masuk halus — CSS kit, hormati prefers-reduced-motion, TIADA JavaScript).',
                'zoom' => 'Kelas .rk-anim-zoom pada <body>; tanda elemen dengan .rk-reveal (zoom masuk halus — CSS kit, TIADA JavaScript).',
                default => 'Tiada animasi.',
            },
            'nada' => Moods::prompt((string) data_get($step2, 'mood', 'tenang_khusyuk')),
            'elemen_islamik' => $islamik,
            'arahan_seni' => [
                'Guna skala tipografi bendalir (kelas .rk-heading-display/.rk-lede — clamp sedia ada).',
                'Bina KEDALAMAN: guna bayang berlapis (rk-shadow-*/kad terapung) & kontras — JANGAN hasilkan reka rata.',
                'Hero berlapis: imej/gradien + overlay + eyebrow + ornamen + tajuk display + butang.',
                'Ruang lega (rk-section) + irama latar berselang terang/alt, dan SEKURANG-KURANGNYA satu seksyen rk-section--dark untuk kontras dramatik.',
                'Hover-lift pada kad (rk-card-hover). Elakkan superlatif kosong & fakta rekaan.',
                'JANGAN tinggalkan elemen kosong menunggu data (tiada jadual/tbody kosong, tiada "akan diisi").',
            ],
        ];
    }

    /**
     * @param  array<string,array{0:string,1:string}>  $map  nilai => [kelas_kit, nota]
     * @return array{nilai:string, kelas_kit:string, nota:string}
     */
    private function describe(string $value, array $map): array
    {
        [$kelas, $nota] = $map[$value] ?? ['', ''];

        return ['nilai' => $value, 'kelas_kit' => $kelas, 'nota' => $nota];
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

    /** Placeholder verbatim (bersyarat data/tier) — pelayan (HtmlDraftFinisher) ganti dgn data sebenar. */
    private function placeholderSpec(Project $project): string
    {
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();
        $l4 = $sections['step_4']['panels'] ?? [];
        $enabled = $project->pages()->where('enabled', true)->pluck('page_key')->all();
        $heroMode = data_get($sections, 'step_6.hero_mode');
        $heroCount = $project->assets()->where('kind', 'hero')->count();

        $lines = [
            '- [[CONTACT_STRIP]] — jalur maklumat hubungan (telefon/emel/alamat/sosial) sebelum footer. WAJIB.',
        ];

        if ($project->assets()->where('kind', 'logo')->exists()) {
            $lines[] = '- [[LOGO]] — letak sebagai src <img> logo di header (dan footer jika ada). Pelayan sisip logo sebenar.';
        }

        $hasBank = filled($l4['infaq']['bank_account'] ?? null) || filled($l4['derma']['bank_account'] ?? null);
        if ($hasBank && (in_array('infaq', $enabled, true) || in_array('derma', $enabled, true))) {
            $lines[] = '- [[BANK_BLOCK]] — blok maklumat bank/QR dalam seksyen Infaq/Derma. WAJIB bila seksyen itu ada.';
        }
        if (! empty($l4['ajk']['members'] ?? [])) {
            $lines[] = '- [[AJK_GRID]] — grid ahli jawatankuasa dalam seksyen AJK (pelayan sisip nama & jawatan).';
        }
        if (filled($l4['perutusan']['name'] ?? null)) {
            $lines[] = '- [[PERUTUSAN_NAMA]] — letak token SAHAJA di bawah petikan perutusan. JANGAN tulis nama ATAU jawatan sendiri — pelayan sisip kedua-duanya.';
        }
        if (in_array($heroMode, ['upload', 'stok_sementara', 'perlu_fotografi'], true)) {
            $lines[] = '- [[HERO_IMAGE]] — letak sebagai nilai src bagi <img class="rk-hero__bg"> hero (pelayan sisip foto sebenar/stok bertema).';
        }
        if ($heroCount > 1) {
            $lines[] = '- [[IMG_SECTION_1]] (dan [[IMG_SECTION_2]] jika sesuai) — src <img> imej tambahan dalam seksyen (pelayan sisip).';
        }
        if (filled(data_get($sections, 'step_6.video_url'))) {
            $lines[] = '- [[VIDEO_LINK]] — letak token ini di tempat butang video pengenalan (pelayan ganti dengan butang).';
        }
        if ($project->tier->isMosque()) {
            $lines[] = '- [[WAKTU_SOLAT]] — kad waktu solat (pelayan sisip paparan statik berlabel JAKIM e-Solat).';
            $lines[] = '- [[AYAT_ARAB]] — tempat satu ayat Al-Quran dalam .rk-verse-box (pelayan sisip teks Arab rasmi; JANGAN tulis aksara Arab sendiri).';
        }

        $lines[] = 'PENTING: HANYA token di atas akan diganti pelayan. JANGAN cipta placeholder/tempat kosong lain, dan JANGAN biarkan jadual/senarai kosong "menunggu data".';

        return implode("\n", $lines);
    }

    private function packageKey(Project $project): string
    {
        return $project->design?->package_key ?: 'warisan_hijau';
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
