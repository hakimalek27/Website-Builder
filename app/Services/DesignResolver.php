<?php

namespace App\Services;

use App\Models\DesignPackage;
use App\Models\Project;
use App\Support\FontPairs;

/**
 * Selesaikan tokens & fonts reka bentuk berkesan (pakej + overrides §6 L2).
 */
class DesignResolver
{
    /** @return array{tokens:array<string,string>, fonts:array<string,string>, layout:string, icon_style:array} */
    public function resolve(Project $project): array
    {
        $design = $project->design;
        $key = $design?->package_key ?: 'warisan_hijau';
        $package = DesignPackage::where('key', $key)->first();

        $tokens = $package?->tokens ?? [];
        $fonts = $package?->fonts ?? FontPairs::fonts(FontPairs::DEFAULT);
        $layout = $package?->layout ?? 'hero-tengah';
        $iconStyle = $package?->icon_style ?? ['weight' => 'sederhana', 'container' => 'bulat-cair', 'stroke_width' => 1.75];

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

        return ['tokens' => $tokens, 'fonts' => $fonts, 'layout' => $layout, 'icon_style' => $iconStyle];
    }
}
