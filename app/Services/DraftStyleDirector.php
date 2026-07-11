<?php

namespace App\Services;

use App\Models\Project;
use App\Support\StockLibrary;
use Illuminate\Support\Facades\File;

/**
 * §Fasa 15 — "pengarah gaya" deterministik. Dari seed projek (crc32 ULID — TIADA storan,
 * boleh-override overrides['style_seed']), hasilkan arahan variasi supaya DUA pelanggan
 * dengan pakej sama TIDAK dapat draf seakan-akan: olahan hero, motif corak, irama latar
 * seksyen, gaya CTA, ornamen, pilihan scene stok, dan blueprint yang dilampir ke prompt.
 */
class DraftStyleDirector
{
    private const HERO_TREATMENTS = [
        'Hero FOTO PENUH: imej [[HERO_IMAGE]] menutupi seluruh hero (rk-hero--foto rk-hero-overlay), teks putih di atas overlay gelap.',
        'Hero BELAH: teks di kiri, imej [[HERO_IMAGE]] dalam bingkai bulat/rounded di kanan (rk-hero--belah).',
        'Hero GRADIEN BERLAPIS: latar gradien primary→primaryDeep dengan corak halus + kotak ayat berkaca (tiada foto besar).',
    ];

    private const MOTIFS = ['dots', 'rub', 'bintang', 'arabesque'];

    private const ORNAMENTS = ['garis-emas', 'lengkung', 'arabesque'];

    private const CTA_STYLES = ['emas', 'primary', 'kaca'];

    private const RHYTHMS = [
        ['terang', 'alt', 'terang', 'gelap', 'terang', 'alt'],
        ['terang', 'gelap', 'alt', 'terang', 'alt', 'gelap'],
        ['alt', 'terang', 'gelap', 'terang', 'alt', 'terang'],
    ];

    /**
     * @return array{seed:int, hero_treatment:string, motif:string, ornament:string,
     *   cta_style:string, section_rhythm:array<int,string>, stock:?array<string,mixed>,
     *   blueprints:array<int,string>, keunikan:string}
     */
    public function directives(Project $project): array
    {
        $seed = $this->seed($project);
        $isNgo = $project->tier->isNgo();
        $orgKind = $isNgo ? 'ngo' : 'masjid';
        $layout = data_get($project->design?->overrides ?? [], 'layout')
            ?: (data_get($project->sections()->where('section_key', 'step_2')->value('data'), 'layout_home', 'hero-tengah'));

        $heroTreatment = self::HERO_TREATMENTS[$seed % count(self::HERO_TREATMENTS)];
        $motif = self::MOTIFS[intdiv($seed, 3) % count(self::MOTIFS)];
        $ornament = self::ORNAMENTS[intdiv($seed, 7) % count(self::ORNAMENTS)];
        $cta = self::CTA_STYLES[intdiv($seed, 11) % count(self::CTA_STYLES)];
        $rhythm = self::RHYTHMS[$seed % count(self::RHYTHMS)];

        // Kategori hero stok ikut jenis org (pelbagai supaya tidak seragam).
        $heroCategories = $isNgo
            ? ['komuniti', 'ngo-kebajikan']
            : ['masjid-eksterior', 'masjid-eksterior', 'masjid-interior'];
        $heroCat = $heroCategories[$seed % count($heroCategories)];
        $stock = StockLibrary::pick($seed, $heroCat, $orgKind, 0);

        return [
            'seed' => $seed,
            'hero_treatment' => $heroTreatment,
            'motif' => $motif,
            'ornament' => $ornament,
            'cta_style' => $cta,
            'section_rhythm' => $rhythm,
            'stock' => $stock,
            'blueprints' => $this->blueprints($layout, $seed),
            'keunikan' => $this->keunikan($seed, $heroTreatment, $motif, $cta),
        ];
    }

    /** Blueprint seksyen terpilih (kandungan HTML beranotasi) untuk dilampir ke prompt P2. */
    public function blueprintBundle(Project $project): string
    {
        $out = [];
        foreach ($this->directives($project)['blueprints'] as $file) {
            $path = resource_path('draft-kit/blueprints/'.$file);
            if (File::exists($path)) {
                $out[] = trim(File::get($path));
            }
        }

        return implode("\n\n", $out);
    }

    /** Scene hero stok terpilih (untuk stok_sementara) — data-URI diwarna palet. */
    public function heroStockDataUri(Project $project, array $tokens): ?string
    {
        $dir = $this->directives($project);
        if (empty($dir['stock']['file'])) {
            return null;
        }

        return StockLibrary::sceneDataUri($dir['stock']['file'], $tokens) ?: null;
    }

    private function seed(Project $project): int
    {
        $override = data_get($project->design?->overrides ?? [], 'style_seed');
        if (is_numeric($override)) {
            return (int) abs((int) $override);
        }

        return (int) (crc32((string) $project->id) & 0x7FFFFFFF);
    }

    /** Blueprint seksyen yang dilampir verbatim ke prompt P2. */
    private function blueprints(string $layout, int $seed): array
    {
        $heroMap = [
            'hero-tengah' => 'hero-tengah.html',
            'hero-belah' => 'hero-belah.html',
            'hero-penuh' => 'hero-penuh.html',
            'hero-mihrab' => 'hero-mihrab.html',
            'klasik-formal' => 'hero-klasik.html',
            'grid-kad' => 'hero-grid.html',
        ];
        $hero = $heroMap[$layout] ?? 'hero-tengah.html';

        // Satu blueprint seksyen "berlatar gelap" bergilir untuk kepelbagaian.
        $sectionPool = ['seksyen-kad.html', 'seksyen-gelap.html', 'cta-jalur.html', 'stat-jalur.html'];

        return [
            $hero,
            $sectionPool[$seed % count($sectionPool)],
            'footer-tiga-lajur.html',
        ];
    }

    private function keunikan(int $seed, string $hero, string $motif, string $cta): string
    {
        return 'KEUNIKAN (jadikan laman ini BERBEZA dari templat generik — jangan hasilkan susunan seragam): '
            .'guna olahan '.mb_strtolower(strtok($hero, ':')).', '
            .'corak Islamik motif "'.$motif.'" pada 1-2 seksyen sahaja (kelas rk-pattern--'.$motif.'), '
            .'butang utama gaya "'.$cta.'". Pastikan irama latar seksyen berselang (terang/alt/gelap) '
            .'dan sekurang-kurangnya SATU seksyen berlatar gelap penuh (rk-section--dark) untuk kontras dramatik. '
            .'Nombor seed reka: '.$seed.'.';
    }
}
