<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Sisip Lucide static SVG terpilih terus dalam Blade (§7.3) — tiada JS runtime.
 * 24 ikon terkurasi disalin ke resources/icons/lucide/ (self-contained).
 */
class Lucide
{
    /** @var array<string, string> */
    private static array $cache = [];

    /**
     * @param  string  $name  Nama PascalCase (cth "HeartHandshake") atau kebab.
     */
    public static function svg(string $name, float $strokeWidth = 2.0, string $class = 'w-6 h-6', int $size = 24): string
    {
        $kebab = Str::kebab($name);
        $path = resource_path("icons/lucide/{$kebab}.svg");

        $raw = self::$cache[$kebab] ??= (File::exists($path) ? File::get($path) : '');

        if ($raw === '') {
            // Fallback: kotak kosong (ikon tidak dijumpai).
            return '<svg viewBox="0 0 24 24" class="'.e($class).'"><rect x="3" y="3" width="18" height="18" rx="3" fill="none" stroke="currentColor" stroke-width="'.$strokeWidth.'"/></svg>';
        }

        $svg = preg_replace('/<!--.*?-->/s', '', $raw);
        $svg = preg_replace('/\sclass="[^"]*"/', '', $svg, 1);
        $svg = preg_replace('/\swidth="[^"]*"/', ' width="'.$size.'"', $svg, 1);
        $svg = preg_replace('/\sheight="[^"]*"/', ' height="'.$size.'"', $svg, 1);
        $svg = preg_replace('/stroke-width="[^"]*"/', 'stroke-width="'.$strokeWidth.'"', $svg);
        // Sisip semula class terpilih pada tag <svg>.
        $svg = preg_replace('/<svg\b/', '<svg class="'.e($class).'"', $svg, 1);

        return trim($svg);
    }

    /** Peta berat garisan → stroke-width (§6 L2). */
    public static function strokeForWeight(string $weight): float
    {
        return match ($weight) {
            'halus' => 1.25,
            'tebal' => 2.25,
            default => 1.75, // sederhana
        };
    }
}
