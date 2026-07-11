<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * §Fasa 15 — Kit Reka Premium. Bina blok <style id="reka-kit"> yang disuntik pelayan
 * ke dalam <head> setiap draf HTML (0 token AI). Isi = :root token --rk-* (dari
 * DesignResolver + PaletteDeriver::ramp) + kandungan kit.css (kelas rk-* premium).
 * Corak Islamik = SVG data-URI (TIADA URL luar — patuh CSP raw default-src 'none').
 */
class DraftKit
{
    private static ?string $cachedKitCss = null;

    /** Token warna asas lalai (selari shell/finisher). */
    private const DEFAULTS = [
        'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
        'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC', 'radius' => '1rem',
    ];

    /**
     * Bina <style id="reka-kit"> penuh untuk draf.
     *
     * @param  array{tokens?:array<string,string>, fonts?:array<string,string>, animations?:string}  $design  output DesignResolver::resolve()
     */
    public static function styleTag(array $design): string
    {
        $t = array_merge(self::DEFAULTS, $design['tokens'] ?? []);
        $ramp = PaletteDeriver::ramp($t);
        $fonts = $design['fonts'] ?? [];
        $body = $fonts['body'] ?? 'Plus Jakarta Sans';
        $display = $fonts['display'] ?? 'Cormorant Garamond';
        $arabic = $fonts['arabic'] ?? 'Amiri';

        $vars = [
            '--rk-primary' => $t['primary'],
            '--rk-primary-dark' => $t['primaryDark'],
            '--rk-primary-deep' => $ramp['primaryDeep'],
            '--rk-primary-light' => $ramp['primaryLight'],
            '--rk-accent' => $t['accent'],
            '--rk-accent-bright' => $ramp['accentBright'],
            '--rk-accent-deep' => $ramp['accentDeep'],
            '--rk-ink' => $t['ink'],
            '--rk-bg' => $t['bg'],
            '--rk-bg-alt' => $t['bgAlt'],
            '--rk-radius' => $t['radius'],
            '--rk-shadow-tint' => $ramp['shadowTint'],
            '--rk-font-body' => "'".$body."',system-ui,sans-serif",
            '--rk-font-display' => "'".$display."',Georgia,serif",
            '--rk-font-arabic' => "'".$arabic."',serif",
            '--rk-pattern-dots' => self::patternDataUri('dots-radial', $t['accent'], $t['primary']),
            '--rk-pattern-rub' => self::patternDataUri('rub-el-hizb', $t['accent']),
            '--rk-pattern-bintang' => self::patternDataUri('bintang-8', $t['accent']),
            '--rk-pattern-arabesque' => self::patternDataUri('arabesque-line', $t['accent']),
        ];

        $root = ':root{';
        foreach ($vars as $k => $v) {
            $root .= $k.':'.$v.';';
        }
        $root .= '}';

        return '<style id="reka-kit">'.$root.self::kitCss().'</style>';
    }

    /** Kit CSS (minified & dicache). */
    public static function kitCss(): string
    {
        if (self::$cachedKitCss !== null) {
            return self::$cachedKitCss;
        }

        $css = File::get(resource_path('draft-kit/kit.css'));

        return self::$cachedKitCss = self::minify($css);
    }

    /**
     * Corak SVG → CSS url("data:image/svg+xml,…"). Ganti __TINT__/__TINT2__ dengan warna,
     * url-encode (patuh CSP; TIADA URL luar).
     */
    public static function patternDataUri(string $motif, string $tint, ?string $tint2 = null): string
    {
        $path = resource_path('draft-kit/patterns/'.$motif.'.svg');
        if (! File::exists($path)) {
            return 'none';
        }
        $svg = trim(File::get($path));
        $svg = str_replace(['__TINT__', '__TINT2__'], [$tint, $tint2 ?? $tint], $svg);

        return 'url("data:image/svg+xml,'.rawurlencode($svg).'")';
    }

    /** Minify CSS konservatif — KEKALKAN ruang sekitar operator (calc/clamp perlukan). */
    private static function minify(string $css): string
    {
        $css = preg_replace('#/\*.*?\*/#s', '', $css) ?? $css;   // buang komen
        $css = preg_replace('/\s+/', ' ', $css) ?? $css;          // runtuh ruang → satu ruang
        $css = preg_replace('/\s*([{};,])\s*/', '$1', $css) ?? $css; // buang ruang sekitar { } ; ,

        return trim($css);
    }
}
