<?php

namespace App\Services;

use App\Models\DesignPackage;
use App\Models\Project;
use App\Support\FontPairs;

/**
 * Selesaikan tokens & fonts reka bentuk berkesan (pakej + overrides §6 L2).
 * Varian struktur (header/footer/card/divider/layout) divalidasi allowlist — nilai
 * tak dikenali jatuh ke default supaya render draf tidak boleh pecah (§7 pelbagaian).
 */
class DesignResolver
{
    public const LAYOUTS = ['hero-tengah', 'hero-belah', 'grid-kad', 'klasik-formal', 'hero-penuh', 'hero-mihrab'];

    public const HEADERS = ['padat', 'gradien', 'tengah'];

    public const FOOTERS = ['ringkas', 'tengah-jenama', 'tiga-lajur'];

    public const CARDS = ['lembut', 'garis', 'terapung'];

    public const DIVIDERS = ['tiada', 'garis-emas', 'lengkung'];

    public const ANIMATIONS = ['tiada', 'fade', 'zoom'];

    /** @return array{tokens:array<string,string>, fonts:array<string,string>, layout:string, icon_style:array, header:string, footer:string, card:string, divider:string, animations:string} */
    public function resolve(Project $project): array
    {
        $design = $project->design;
        $key = $design?->package_key ?: 'warisan_hijau';
        $package = DesignPackage::where('key', $key)->first();

        $tokens = $package?->tokens ?? [];
        $fonts = $package?->fonts ?? FontPairs::fonts(FontPairs::DEFAULT);
        $layout = $package?->layout ?? 'hero-tengah';
        $iconStyle = $package?->icon_style ?? ['weight' => 'sederhana', 'container' => 'bulat-cair', 'stroke_width' => 1.75];
        $variants = $package?->variants ?? [];

        $overrides = $design?->overrides ?? [];

        if (! empty($overrides['palette']) && is_array($overrides['palette'])) {
            $tokens = array_merge($tokens, $overrides['palette']);
        }
        if (! empty($overrides['font_pair']) && FontPairs::has($overrides['font_pair'])) {
            $fonts = array_merge($fonts, FontPairs::fonts($overrides['font_pair']));
        }
        if (! empty($overrides['icon_style']) && is_array($overrides['icon_style'])) {
            $iconStyle = array_merge($iconStyle, $overrides['icon_style']);
        }
        if (! empty($overrides['layout'])) {
            $layout = $overrides['layout'];
        }
        if (! empty($overrides['arabic_font'])) {
            $fonts['arabic'] = $overrides['arabic_font'];
        }

        return [
            'tokens' => $tokens,
            'fonts' => $fonts,
            'layout' => $this->pick($layout, self::LAYOUTS, 'hero-tengah'),
            'icon_style' => $iconStyle,
            'header' => $this->pick($overrides['header_style'] ?? $variants['header'] ?? null, self::HEADERS, 'padat'),
            'footer' => $this->pick($overrides['footer_style'] ?? $variants['footer'] ?? null, self::FOOTERS, 'ringkas'),
            'card' => $this->pick($overrides['card_style'] ?? $variants['card'] ?? null, self::CARDS, 'lembut'),
            'divider' => $this->pick($overrides['divider'] ?? $variants['divider'] ?? null, self::DIVIDERS, 'tiada'),
            'animations' => $this->animationVariant($overrides['animations'] ?? null),
        ];
    }

    /** Legasi: boolean lama (true→fade, false→tiada) atau string varian; nilai luar allowlist → tiada. */
    private function animationVariant(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'fade' : 'tiada';
        }

        return in_array($value, self::ANIMATIONS, true) ? $value : 'tiada';
    }

    /** Pulangkan $value jika dalam allowlist, jika tidak $default. */
    private function pick(?string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
