<?php

namespace App\Support;

/**
 * Terbitkan palet reka penuh + kawalan kontras WCAG dari 2 warna pilihan PIC (§6 L2, mod custom).
 * PHP tulen (tiada pustaka JS) — patuh CSP. Guna ruang HSL untuk terbitan harmoni.
 */
class PaletteDeriver
{
    public const MIN_CONTRAST = 4.5;

    public static function isValidHex(?string $hex): bool
    {
        return is_string($hex) && preg_match('/^#[0-9A-Fa-f]{6}$/', $hex) === 1;
    }

    /**
     * Terbitkan 6 token dari primary + accent, dengan primary dijamin boleh-baca.
     *
     * @return array{tokens: array<string,string>, adjusted: bool}
     */
    public static function derive(string $primary, string $accent): array
    {
        [$h, $s] = self::hexToHsl($primary);

        $tokens = [
            'primary' => strtoupper($primary),
            'primaryDark' => '#000000',              // dikira semula dlm ensureReadable
            'accent' => strtoupper($accent),
            'ink' => self::hslToHex($h, 0.25, 0.11),
            'bg' => self::hslToHex($h, 0.18, 0.97),
            'bgAlt' => self::hslToHex($h, 0.22, 0.92),
        ];

        return self::ensureReadable($tokens);
    }

    /** Gelapkan primary langkah demi langkah sehingga kontras cukup atas bg & untuk teks putih. */
    public static function ensureReadable(array $tokens): array
    {
        $original = $tokens['primary'];
        [$h, $s, $l] = self::hexToHsl($original);
        $adjusted = false;

        // Sehingga 50 langkah (2% L setiap satu) — cukup untuk gelapkan warna paling cerah ke ~hitam.
        for ($i = 0; $i < 50; $i++) {
            // Kekalkan hex asal jika belum dilaras (elak drift pembulatan HSL round-trip).
            $primary = $adjusted ? self::hslToHex($h, $s, $l) : $original;
            $onBg = self::contrastRatio($primary, $tokens['bg']);
            $whiteOn = self::contrastRatio('#FFFFFF', $primary);
            $tokens['primary'] = $primary;

            if ($onBg >= self::MIN_CONTRAST && $whiteOn >= self::MIN_CONTRAST) {
                break;
            }
            $l = max(0.0, $l - 0.02);
            $adjusted = true;
        }

        // primaryDark sentiasa sedikit lebih gelap dari primary akhir.
        [$ph, $ps, $pl] = self::hexToHsl($tokens['primary']);
        $tokens['primaryDark'] = self::hslToHex($ph, min(1.0, $ps), max(0.06, $pl * 0.62));

        return ['tokens' => $tokens, 'adjusted' => $adjusted];
    }

    /**
     * §Fasa 15 — terbitkan ramp warna lanjutan (palet 7-peranan) untuk Kit Reka Premium.
     * Menjamin accentBright boleh-baca atas primaryDark & accentDeep atas bg (WCAG AA ≥ 4.5),
     * meniru sistem token mamkl.my (primary/deep/light + accent/bright/deep). PHP tulen (CSP).
     *
     * @param  array<string,string>  $tokens  perlu primary, primaryDark, accent, bg
     * @return array{primaryDeep:string, primaryLight:string, accentBright:string, accentDeep:string, shadowTint:string}
     */
    public static function ramp(array $tokens): array
    {
        $primary = self::isValidHex($tokens['primary'] ?? null) ? $tokens['primary'] : '#1B5E3F';
        $primaryDark = self::isValidHex($tokens['primaryDark'] ?? null) ? $tokens['primaryDark'] : '#0F3D27';
        $accent = self::isValidHex($tokens['accent'] ?? null) ? $tokens['accent'] : '#C9A961';
        $bg = self::isValidHex($tokens['bg'] ?? null) ? $tokens['bg'] : '#FAF7F2';

        // primaryDeep: lebih gelap dari primaryDark — untuk hero CTA / jubin gelap.
        [$dh, $ds, $dl] = self::hexToHsl($primaryDark);
        $primaryDeep = self::hslToHex($dh, min(1.0, $ds), max(0.05, $dl * 0.62));

        // primaryLight: lebih cerah dari primary — aksen halus / keadaan hover.
        [$ph, $ps, $pl] = self::hexToHsl($primary);
        $primaryLight = self::hslToHex($ph, $ps, min(0.55, $pl + 0.12));

        // accentBright: cerahkan accent (langkah 2% L, maks 50) sehingga boleh-baca ATAS primaryDark.
        [$ah, $as] = self::hexToHsl($accent);
        $al = self::hexToHsl($accent)[2];
        $accentBright = $accent;
        for ($i = 0, $l = $al; $i < 50; $i++) {
            if (self::contrastRatio($accentBright, $primaryDark) >= self::MIN_CONTRAST) {
                break;
            }
            $l = min(1.0, $l + 0.02);
            $accentBright = self::hslToHex($ah, $as, $l);
        }

        // accentDeep: gelapkan accent sehingga boleh-baca ATAS bg (teks emas atas krim).
        $accentDeep = $accent;
        for ($i = 0, $l = $al; $i < 50; $i++) {
            if (self::contrastRatio($accentDeep, $bg) >= self::MIN_CONTRAST) {
                break;
            }
            $l = max(0.0, $l - 0.02);
            $accentDeep = self::hslToHex($ah, $as, $l);
        }

        // shadowTint: RGB primaryDark untuk bayang bertinta jenama — rgb(<tint> / a).
        [$r, $g, $b] = self::hexToRgb($primaryDark);
        $shadowTint = "$r $g $b";

        return [
            'primaryDeep' => $primaryDeep,
            'primaryLight' => $primaryLight,
            'accentBright' => $accentBright,
            'accentDeep' => $accentDeep,
            'shadowTint' => $shadowTint,
        ];
    }

    /** Nisbah kontras WCAG 2.x antara dua warna hex (1.0–21.0). */
    public static function contrastRatio(string $hex1, string $hex2): float
    {
        $l1 = self::relativeLuminance($hex1);
        $l2 = self::relativeLuminance($hex2);
        [$hi, $lo] = $l1 >= $l2 ? [$l1, $l2] : [$l2, $l1];

        return ($hi + 0.05) / ($lo + 0.05);
    }

    private static function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        $chan = fn ($c) => ($c <= 0.03928) ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $chan($r / 255) + 0.7152 * $chan($g / 255) + 0.0722 * $chan($b / 255);
    }

    /** @return array{0:int,1:int,2:int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [(int) hexdec(substr($hex, 0, 2)), (int) hexdec(substr($hex, 2, 2)), (int) hexdec(substr($hex, 4, 2))];
    }

    /** @return array{0:float,1:float,2:float} h∈[0,360) s,l∈[0,1] */
    private static function hexToHsl(string $hex): array
    {
        [$r, $g, $b] = array_map(fn ($c) => $c / 255, self::hexToRgb($hex));
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d == 0.0) {
            return [0.0, 0.0, $l];
        }

        $s = $d / (1 - abs(2 * $l - 1));
        $h = match (true) {
            $max === $r => fmod(($g - $b) / $d, 6),
            $max === $g => (($b - $r) / $d) + 2,
            default => (($r - $g) / $d) + 4,
        };
        $h *= 60;
        if ($h < 0) {
            $h += 360;
        }

        return [$h, $s, $l];
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0.0],
            $h < 120 => [$x, $c, 0.0],
            $h < 180 => [0.0, $c, $x],
            $h < 240 => [0.0, $x, $c],
            $h < 300 => [$x, 0.0, $c],
            default => [$c, 0.0, $x],
        };

        return sprintf('#%02X%02X%02X',
            (int) round(($r + $m) * 255),
            (int) round(($g + $m) * 255),
            (int) round(($b + $m) * 255),
        );
    }
}
