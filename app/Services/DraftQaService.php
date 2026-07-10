<?php

namespace App\Services;

use App\Models\Project;
use App\Support\PageCatalog;
use App\Support\PaletteDeriver;

/**
 * §Fasa 14 — QA auto pasca-jana untuk draf saluran HTML.
 *
 * Menyemak HTML SIAP: (a) setiap halaman dipilih wujud sebagai seksyen; (b) kontras
 * warna token memenuhi WCAG AA (≥ 4.5:1); (c) kontras warna inline yang ditulis AI
 * (lapor sahaja). Laporan disimpan dalam input_snapshot['qa'] dan — jika ada isu —
 * admin dimaklumkan. QA TIDAK PERNAH menghalang draf (dipanggil dalam try/catch di Job).
 */
class DraftQaService
{
    /** Bilangan maksimum isu kontras inline dilaporkan (jaga saiz snapshot). */
    private const MAX_INLINE_ISSUES = 10;

    public function __construct(private DesignResolver $designResolver) {}

    /**
     * @return array{passed: bool, issues: array<int, array<string, mixed>>, checked_at: string}
     */
    public function analyse(Project $project, string $finalHtml): array
    {
        $issues = [
            ...$this->checkSections($project, $finalHtml),
            ...$this->checkTokenContrast($project),
            ...$this->checkInlineContrast($finalHtml),
        ];

        return [
            'passed' => $issues === [],
            'issues' => array_values($issues),
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
            // Fallback 1: label muncul sebagai teks (draf legasi / AI drift).
            if ($label !== '' && stripos($html, $label) !== false) {
                continue;
            }
            // Fallback 2 khas: halaman utama sering diberi id hero/home.
            if ($key === 'utama' && ($this->hasId($html, 'hero') || $this->hasId($html, 'home'))) {
                continue;
            }

            $issues[] = [
                'type' => 'missing_section',
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

        // Pasangan [nama, teks, latar] yang betul-betul dirender (rujuk shell.blade).
        $pairs = [
            ['ink/bg', $t['ink'] ?? null, $t['bg'] ?? null],
            ['primary/bg', $t['primary'] ?? null, $t['bg'] ?? null],
            ['putih/primary', '#FFFFFF', $t['primary'] ?? null],
            // primaryDark ATAS accent = teks butang .btn-accent (BUKAN accent atas bg — accent hiasan).
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
                    'pair' => $name,
                    'ratio' => round($ratio, 2),
                    'mesej' => "Kontras rendah {$name}: ".round($ratio, 2).':1 (patut ≥ '.PaletteDeriver::MIN_CONTRAST.':1).',
                ];
            }
        }

        return $issues;
    }

    /** (c) Kontras warna inline yang AI tulis (color + background dalam satu atribut). Lapor sahaja. */
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

    private function hasId(string $html, string $id): bool
    {
        return (bool) preg_match('/id\s*=\s*["\']'.preg_quote($id, '/').'["\']/i', $html);
    }

    /** Kembangkan #abc → #aabbcc; kembalikan null jika bukan 3/6-digit hex sah. */
    private function expandHex(string $hex): ?string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return strlen($hex) === 6 ? '#'.strtoupper($hex) : null;
    }
}
