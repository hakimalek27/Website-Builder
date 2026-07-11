<?php

namespace App\Services;

use App\Models\Project;
use App\Support\PageCatalog;
use App\Support\PaletteDeriver;

/**
 * §Fasa 14 — QA auto pasca-jana untuk draf saluran HTML.
 * §Fasa 15 — QA premium: (a) issues STRUKTURAL diperluas (logo/hero/elemen Islamik hilang,
 *   tahun salah, tbody kosong, kit hilang); (b) SUGGESTIONS estetik (kualiti premium —
 *   guna HTML MENTAH supaya kit sendiri tidak melepaskan kiraan). `passed` = issues sahaja
 *   (semantik notifikasi kekal). Suggestions + issues polishable → cetus auto-polish.
 * QA TIDAK PERNAH menghalang draf (dipanggil dalam try/catch di Job).
 */
class DraftQaService
{
    private const MAX_INLINE_ISSUES = 10;

    /** Ambang saiz raw sebelum suggestions estetik dikira (fixture kecil terlepas). */
    private const MIN_RAW_FOR_AESTHETIC = 15000;

    public function __construct(private DesignResolver $designResolver) {}

    /**
     * @return array{passed: bool, issues: array<int, array<string, mixed>>, suggestions: array<int, array<string, mixed>>, checked_at: string}
     */
    public function analyse(Project $project, string $finalHtml, ?string $raw = null): array
    {
        $issues = [
            ...$this->checkSections($project, $finalHtml),
            ...$this->checkTokenContrast($project),
            ...$this->checkInlineContrast($finalHtml),
            ...$this->checkPromise($project, $finalHtml, $raw),
            ...$this->checkPolishArtifacts($finalHtml, $raw),
        ];

        return [
            'passed' => $issues === [],
            'issues' => array_values($issues),
            'suggestions' => array_values($this->suggestions($project, $raw)),
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /** (a) Setiap halaman dipilih hadir sebagai seksyen (id=page_key, atau fallback label/hero). */
    private function checkSections(Project $project, string $html): array
    {
        $meta = PageCatalog::metaFor($project->tier);
        $issues = [];

        foreach ($project->pages()->where('enabled', true)->orderBy('sort')->get() as $p) {
            $key = $p->page_key;
            $label = $p->custom_name ?: ($meta[$key]['label'] ?? $key);

            if ($this->hasId($html, $key)) {
                continue;
            }
            if ($label !== '' && stripos($html, $label) !== false) {
                continue;
            }
            if ($key === 'utama' && ($this->hasId($html, 'hero') || $this->hasId($html, 'home'))) {
                continue;
            }

            $issues[] = [
                'type' => 'missing_section',
                'category' => 'structural',
                'polishable' => true,
                'page_key' => $key,
                'mesej' => "Seksyen \"{$label}\" ({$key}) tidak ditemui dalam draf.",
            ];
        }

        return $issues;
    }

    /** (b) Kontras token yang benar-benar dirender sebagai teks (WCAG AA ≥ 4.5:1). */
    private function checkTokenContrast(Project $project): array
    {
        $t = $this->designResolver->resolve($project)['tokens'] ?? [];
        $issues = [];

        $pairs = [
            ['ink/bg', $t['ink'] ?? null, $t['bg'] ?? null],
            ['primary/bg', $t['primary'] ?? null, $t['bg'] ?? null],
            ['putih/primary', '#FFFFFF', $t['primary'] ?? null],
            ['primaryDark/accent', $t['primaryDark'] ?? null, $t['accent'] ?? null],
        ];

        foreach ($pairs as [$name, $fg, $bg]) {
            if (! PaletteDeriver::isValidHex($fg) || ! PaletteDeriver::isValidHex($bg)) {
                continue;
            }
            $ratio = PaletteDeriver::contrastRatio($fg, $bg);
            if ($ratio < PaletteDeriver::MIN_CONTRAST) {
                $issues[] = [
                    'type' => 'low_contrast',
                    'category' => 'structural',
                    'polishable' => false,   // palet — bukan boleh dibaiki oleh polish AI
                    'pair' => $name,
                    'ratio' => round($ratio, 2),
                    'mesej' => "Kontras rendah {$name}: ".round($ratio, 2).':1 (patut ≥ '.PaletteDeriver::MIN_CONTRAST.':1).',
                ];
            }
        }

        return $issues;
    }

    /** (c) Kontras warna inline yang AI tulis (color + background dalam satu atribut). */
    private function checkInlineContrast(string $html): array
    {
        if (! preg_match_all('/style\s*=\s*"([^"]*)"/i', $html, $m)) {
            return [];
        }

        $issues = [];
        $seen = [];
        foreach ($m[1] as $style) {
            if (! preg_match('/(?<!-)\bcolor\s*:\s*(#[0-9A-Fa-f]{3,6})\b/', $style, $fgM)) {
                continue;
            }
            if (! preg_match('/\bbackground(?:-color)?\s*:\s*(#[0-9A-Fa-f]{3,6})\b/', $style, $bgM)) {
                continue;
            }
            $fg = $this->expandHex($fgM[1]);
            $bg = $this->expandHex($bgM[1]);
            if ($fg === null || $bg === null) {
                continue;
            }
            $sig = $fg.'|'.$bg;
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;

            $ratio = PaletteDeriver::contrastRatio($fg, $bg);
            if ($ratio < PaletteDeriver::MIN_CONTRAST) {
                $issues[] = [
                    'type' => 'low_contrast_inline',
                    'category' => 'structural',
                    'polishable' => true,
                    'pair' => strtoupper($fg).' atas '.strtoupper($bg),
                    'ratio' => round($ratio, 2),
                    'mesej' => 'Kontras teks inline rendah ('.strtoupper($fg).' atas '.strtoupper($bg).'): '.round($ratio, 2).':1.',
                ];
            }
            if (count($issues) >= self::MAX_INLINE_ISSUES) {
                break;
            }
        }

        return $issues;
    }

    /**
     * §Fasa 15 (d) — janji PIC ditunaikan: logo, imej hero, elemen Islamik.
     * Guna RAW (token) untuk logo/hero (isyarat bersih: AI letak token?), final untuk Islamik.
     */
    private function checkPromise(Project $project, string $final, ?string $raw): array
    {
        $issues = [];
        $sections = $project->sections()->get()->mapWithKeys(fn ($s) => [$s->section_key => $s->data])->all();

        if ($raw !== null) {
            if ($project->assets()->where('kind', 'logo')->exists() && ! str_contains($raw, '[[LOGO]]')) {
                $issues[] = ['type' => 'logo_missing', 'category' => 'structural', 'polishable' => true,
                    'mesej' => 'Logo dimuat naik tetapi model tidak meletakkannya (token [[LOGO]] tiada di header/footer).'];
            }
            $heroMode = data_get($sections, 'step_6.hero_mode');
            if (in_array($heroMode, ['upload', 'stok_sementara', 'perlu_fotografi'], true) && ! str_contains($raw, '[[HERO_IMAGE]]')) {
                $issues[] = ['type' => 'hero_image_missing', 'category' => 'structural', 'polishable' => true,
                    'mesej' => 'Imej hero dijangka tetapi model tidak meletakkan <img> [[HERO_IMAGE]] di hero.'];
            }
        }

        $islamikChosen = (bool) data_get($sections, 'step_2.islamic_elements.corak_geometri', false)
            || (bool) data_get($sections, 'step_2.islamic_elements.pembatas_arabesque', false);
        if ($islamikChosen && ! preg_match('/rk-pattern|rk-ornament|rk-divider--arabesque/i', $final)) {
            $issues[] = ['type' => 'islamic_missing', 'category' => 'structural', 'polishable' => true,
                'mesej' => 'Elemen Islamik dipilih tetapi tiada corak/ornamen (rk-pattern/rk-ornament/rk-divider) dalam draf.'];
        }

        return $issues;
    }

    /** §Fasa 15 — penjaga regresi finisher: tahun salah, tbody kosong, kit hilang. */
    private function checkPolishArtifacts(string $final, ?string $raw): array
    {
        $issues = [];

        if (preg_match('/(?:&copy;|©|[Hh]ak\s*[Cc]ipta)[^<]{0,40}?(20\d{2})/u', $final, $m) && (int) $m[1] !== (int) now()->year) {
            $issues[] = ['type' => 'wrong_year', 'category' => 'structural', 'polishable' => false,
                'mesej' => 'Tahun hak cipta ('.$m[1].') bukan tahun semasa ('.now()->year.').'];
        }
        if (preg_match('/<tbody\b[^>]*>\s*(<!--.*?-->)?\s*<\/tbody>/is', $final)) {
            $issues[] = ['type' => 'empty_tbody', 'category' => 'structural', 'polishable' => false,
                'mesej' => 'Terdapat jadual/tbody kosong "menunggu data".'];
        }
        if ($raw !== null && ! str_contains($final, 'id="reka-kit"')) {
            $issues[] = ['type' => 'kit_missing', 'category' => 'structural', 'polishable' => false,
                'mesej' => 'Kit Reka (<style id="reka-kit">) tidak disuntik ke draf.'];
        }

        return $issues;
    }

    /**
     * §Fasa 15 — suggestions estetik (naik taraf, bukan halangan). Dikira atas RAW (pra-kit)
     * hanya bila cukup besar. Isyarat UTAMA = penggunaan kit rendah; hanya bila kit tak
     * diguna, kira metrik kedalaman mentah (elak false-flag draf yang bersandar pada kit).
     */
    private function suggestions(Project $project, ?string $raw): array
    {
        if ($raw === null || strlen($raw) < self::MIN_RAW_FOR_AESTHETIC) {
            return [];
        }

        $out = [];
        $lower = strtolower($raw);
        $rkCount = substr_count($raw, 'rk-');

        if ($rkCount < 10) {
            $out[] = ['type' => 'low_kit_usage', 'category' => 'aesthetic', 'mesej' => 'Kurang guna kelas Kit REKA (rk-*) — guna kit untuk kedalaman, bayang & konsistensi premium.'];
            if (substr_count($lower, 'box-shadow') < 3) {
                $out[] = ['type' => 'low_depth', 'category' => 'aesthetic', 'mesej' => 'Tambah kedalaman — bayang berlapis pada kad/hero (bukan reka rata).'];
            }
            if (substr_count($raw, 'clamp(') < 2) {
                $out[] = ['type' => 'no_fluid_type', 'category' => 'aesthetic', 'mesej' => 'Guna tipografi bendalir clamp() untuk skala responsif premium.'];
            }
            if (substr_count($lower, 'gradient') < 2) {
                $out[] = ['type' => 'few_gradients', 'category' => 'aesthetic', 'mesej' => 'Tambah gradien/tekstur halus untuk kekayaan visual.'];
            }
        }

        $anim = $this->designResolver->resolve($project)['animations'] ?? 'tiada';
        if ($anim !== 'tiada' && substr_count($lower, '@keyframes') === 0 && stripos($raw, 'rk-anim') === false) {
            $out[] = ['type' => 'no_animation', 'category' => 'aesthetic', 'mesej' => 'Animasi dipilih tetapi tiada — guna kelas .rk-anim-'.$anim.' + .rk-reveal.'];
        }

        return $out;
    }

    private function hasId(string $html, string $id): bool
    {
        return (bool) preg_match('/id\s*=\s*["\']'.preg_quote($id, '/').'["\']/i', $html);
    }

    private function expandHex(string $hex): ?string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return strlen($hex) === 6 ? '#'.strtoupper($hex) : null;
    }
}
