<?php

namespace App\Support;

/**
 * Pasangan font (§6 L2) — SATU sumber kebenaran untuk wizard, pratonton, resolver & draf.
 *
 * - fonts()        → nama famili "Google" (dipakai shell draf via Google Fonts + spec.json).
 * - previewFonts() → nama famili fontsource (di-hos-sendiri) untuk pratonton wizard (CSP 'self').
 *
 * Sebab pratonton dulu hanya bertindak balas untuk pasangan A: font B/C/D tidak dibundel,
 * jadi jatuh ke serif generik. Kini semua famili di-hos-sendiri.
 */
class FontPairs
{
    /** @var array<string, array{label:string, body:string, display:string}> */
    private const PAIRS = [
        'A' => ['label' => 'Jakarta + Cormorant', 'body' => 'Plus Jakarta Sans', 'display' => 'Cormorant Garamond'],
        'B' => ['label' => 'Inter + Playfair', 'body' => 'Inter', 'display' => 'Playfair Display'],
        'C' => ['label' => 'Figtree + Lora', 'body' => 'Figtree', 'display' => 'Lora'],
        'D' => ['label' => 'IBM Plex', 'body' => 'IBM Plex Sans', 'display' => 'IBM Plex Serif'],
        'E' => ['label' => 'Nunito + Fraunces', 'body' => 'Nunito Sans', 'display' => 'Fraunces'],
        'F' => ['label' => 'Manrope + Marcellus', 'body' => 'Manrope', 'display' => 'Marcellus'],
        'G' => ['label' => 'Source Sans + Serif', 'body' => 'Source Sans 3', 'display' => 'Source Serif 4'],
        'H' => ['label' => 'Work Sans + DM Serif', 'body' => 'Work Sans', 'display' => 'DM Serif Display'],
        'I' => ['label' => 'Rubik + Bitter', 'body' => 'Rubik', 'display' => 'Bitter'],
        'J' => ['label' => 'Albert + Zilla Slab', 'body' => 'Albert Sans', 'display' => 'Zilla Slab'],
    ];

    /** Famili yang di-hos sebagai font "variable" fontsource → nama CSS bersuffiks " Variable". */
    private const VARIABLE = [
        'Plus Jakarta Sans', 'Inter', 'Playfair Display', 'Figtree', 'Lora',
        'Nunito Sans', 'Fraunces', 'Manrope', 'Source Sans 3', 'Source Serif 4',
        'Work Sans', 'Rubik', 'Bitter', 'Albert Sans',
    ];

    public const DEFAULT = 'A';

    /** @return array<int, string> */
    public static function keys(): array
    {
        return array_keys(self::PAIRS);
    }

    /** @return array<string, string> pair => label (untuk radio wizard). */
    public static function options(): array
    {
        return array_map(fn ($p) => $p['label'], self::PAIRS);
    }

    /** Nama famili "Google" untuk pasangan (shell draf + spec). @return array{body:string, display:string} */
    public static function fonts(string $pair): array
    {
        $p = self::PAIRS[$pair] ?? self::PAIRS[self::DEFAULT];

        return ['body' => $p['body'], 'display' => $p['display']];
    }

    /** Nama famili fontsource untuk pratonton wizard. @return array{body:string, display:string} */
    public static function previewFonts(string $pair): array
    {
        $f = self::fonts($pair);

        return ['body' => self::previewFamily($f['body']), 'display' => self::previewFamily($f['display'])];
    }

    /** Tukar nama famili "Google" → nama fontsource (variable dapat suffiks " Variable"). */
    public static function previewFamily(string $family): string
    {
        return in_array($family, self::VARIABLE, true) ? $family.' Variable' : $family;
    }

    public static function has(string $pair): bool
    {
        return isset(self::PAIRS[$pair]);
    }
}
