<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * §Fasa 15 — pustaka imej stok "crafted" (scene SVG milik REKA, bertema masjid/NGO,
 * boleh-warna ikut palet projek — TIADA isu lesen/kandungan pihak ketiga). Juga:
 *  - re-encode imej hero muat naik (>1.5MB) → data-URI (selesai had lama yang senyapkan hero).
 * Pemilihan deterministik (seed projek) → anti-pendua antara pelanggan.
 */
class StockLibrary
{
    /** @var array<int,array<string,mixed>>|null */
    private static ?array $scenes = null;

    /** @return array<int,array<string,mixed>> */
    public static function scenes(): array
    {
        if (self::$scenes !== null) {
            return self::$scenes;
        }
        $path = resource_path('draft-kit/stock/manifest.json');
        $data = File::exists($path) ? json_decode(File::get($path), true) : [];

        return self::$scenes = is_array($data['scenes'] ?? null) ? $data['scenes'] : [];
    }

    /**
     * Pilih satu scene stok deterministik (seed + slot) mengikut kategori & jenis org.
     * $orgKind = 'masjid' | 'ngo'. Fallback: mana-mana scene padan org.
     *
     * @return array<string,mixed>|null
     */
    public static function pick(int $seed, string $category, string $orgKind = 'masjid', int $slot = 0): ?array
    {
        $orgOk = fn (array $m): bool => in_array($m['tier'] ?? 'semua', ['semua', $orgKind], true);

        $matches = array_values(array_filter(
            self::scenes(),
            fn ($m) => ($m['category'] ?? '') === $category && $orgOk($m),
        ));
        if ($matches === []) {
            $matches = array_values(array_filter(self::scenes(), $orgOk));
        }
        if ($matches === []) {
            return null;
        }

        return $matches[abs($seed + $slot) % count($matches)];
    }

    /**
     * Scene SVG bertema → data-URI, diwarna ikut palet projek (token __P__/__PD__/__PDEEP__/
     * __A__/__AB__/__BG__). Patuh CSP raw (img-src data:).
     *
     * @param  array<string,string>  $tokens  token DesignResolver (primary/primaryDark/accent/bg…)
     */
    public static function sceneDataUri(string $file, array $tokens): string
    {
        $path = resource_path('draft-kit/stock/'.$file);
        if (! File::exists($path)) {
            return '';
        }
        $t = array_merge(['primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961', 'bg' => '#FAF7F2'], $tokens);
        $ramp = PaletteDeriver::ramp($t);

        $svg = strtr(File::get($path), [
            '__PDEEP__' => $ramp['primaryDeep'],
            '__PD__' => $t['primaryDark'],
            '__P__' => $t['primary'],
            '__AB__' => $ramp['accentBright'],
            '__A__' => $t['accent'],
            '__BG__' => $t['bg'],
        ]);
        $svg = trim(preg_replace('/\s+/', ' ', $svg) ?? $svg);

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }

    /**
     * Re-encode imej raster (hero muat naik) → data-URI JPEG (buang EXIF, resize, kompres
     * ke sasaran ~KB). Menyelesaikan had lama 1.5MB yang menyenyapkan hero jadi gradien.
     */
    public static function reencodeToDataUri(string $storagePath, int $maxW = 1600, int $targetKb = 250): ?string
    {
        if (! Storage::disk('local')->exists($storagePath)) {
            return null;
        }

        try {
            $manager = new ImageManager(new Driver);        // GD DIKUNCI (§11.4 — buang metadata)
            $image = $manager->decodePath(Storage::disk('local')->path($storagePath));
            $image->scaleDown($maxW, $maxW);

            $data = '';
            foreach ([72, 60, 50, 42] as $q) {
                $data = (string) $image->toJpeg($q);
                if (strlen($data) <= $targetKb * 1024) {
                    break;
                }
            }

            return $data === '' ? null : 'data:image/jpeg;base64,'.base64_encode($data);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }
}
