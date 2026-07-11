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

    /** Rujukan ringkas kelas kit untuk dilampir ke prompt penjana (P2). */
    public static function cheatSheet(): string
    {
        return <<<'TXT'
KELAS KIT REKA (disuntik pelayan — GUNA terus, JANGAN takrif semula pemboleh ubah --rk-*):
• Kontena: .rk-container / .rk-container-narrow · Seksyen: .rk-section [+ .rk-section--alt latar selang | + .rk-section--dark latar GELAP kontras dramatik] · .rk-center
• Tipografi (skala bendalir clamp sedia ada): .rk-heading-display (hero) .rk-heading-1/2/3 .rk-lede (perenggan intro besar) .rk-body .rk-dropcap (huruf pertama besar)
• Hero: .rk-hero + SATU dari .rk-hero--tengah|belah|penuh|mihrab|klasik|grid. Foto penuh: tambah .rk-hero--foto .rk-hero-overlay dengan <img class="rk-hero__bg" src="[[HERO_IMAGE]]" alt="">. Kandungan dalam .rk-hero__inner.
• Eyebrow pil: .rk-eyebrow (atas terang) / .rk-eyebrow--on-dark (atas latar gelap)
• Ornamen: <div class="rk-ornament"><span class="rk-ornament-mark"></span></div> · Pembatas: .rk-divider--garis-emas|lengkung|arabesque
• Butang: .rk-btn + .rk-btn--primary|emas|kaca|garis · Baris butang: .rk-btn-row
• Kad: .rk-card + .rk-card--lembut|garis|terapung (+ .rk-card-hover untuk angkat bila hover) · Tajuk: .rk-card__title
• Grid auto-responsif (tanpa media query): .rk-grid (atau .rk-grid--2 / .rk-grid--4) · Dua lajur: .rk-split
• Statistik: <div class="rk-stat"><div class="rk-stat__value">…</div><div class="rk-stat__label">…</div></div>
• Ikon bekas: <span class="rk-icon rk-icon--bulat-cair|bulat-penuh|kotak-lembut|kotak-tegas|heksagon|garis|tanpa-bekas"> …SVG inline… </span>
• Kotak ayat: <div class="rk-verse-box"><p class="rk-arabic">[[AYAT_ARAB]]</p>…</div>
• Corak Islamik latar: tambah .rk-pattern .rk-pattern--dots|rub|bintang|arabesque pada seksyen (hiasan halus)
• Header lekat: .rk-header > .rk-header__inner (.rk-container) > .rk-header__brand + .rk-header__nav · Varian: --gradien | --tengah
• Footer: .rk-footer + .rk-footer--ringkas|tengah-jenama|tiga-lajur (guna .rk-footer__cols untuk 3 lajur)
• Bayang: .rk-shadow-soft|elev|deep · Animasi: letak kelas .rk-anim-fade atau .rk-anim-zoom pada <body>; tanda elemen dengan .rk-reveal
TXT;
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
