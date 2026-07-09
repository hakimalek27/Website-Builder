<?php

namespace Database\Seeders;

use App\Models\DesignPackage;
use Illuminate\Database\Seeder;

/**
 * 5 pakej reka bentuk (§7.2). Tokens hex TEPAT; radius/headerStyle lalai §7.1.
 * icon_style: berat garisan (halus 1.25 / sederhana 1.75 / tebal 2.25) + bekas.
 */
class DesignPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'key' => 'warisan_hijau',
                'name' => 'Warisan Hijau',
                'tokens' => [
                    'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
                    'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC',
                    'radius' => '1rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Plus Jakarta Sans', 'display' => 'Cormorant Garamond', 'arabic' => 'Amiri'],
                'layout' => 'hero-tengah',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'bulat-cair'],
                'preview_meta' => ['suitable_for' => 'Semua — token terbukti produksi mamkl.my'],
            ],
            [
                'key' => 'biru_nilam',
                'name' => 'Biru Nilam',
                'tokens' => [
                    'primary' => '#1D4E89', 'primaryDark' => '#10315C', 'accent' => '#B08D3E',
                    'ink' => '#16202B', 'bg' => '#F7FAFC', 'bgAlt' => '#E8EFF5',
                    'radius' => '1rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Inter', 'display' => 'Playfair Display', 'arabic' => 'Amiri'],
                'layout' => 'hero-belah',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'kotak-lembut'],
                'preview_meta' => ['suitable_for' => 'Masjid bandar moden'],
            ],
            [
                'key' => 'emas_kubah',
                'name' => 'Emas Kubah',
                'tokens' => [
                    'primary' => '#8C6D2F', 'primaryDark' => '#5C4620', 'accent' => '#1B5E3F',
                    'ink' => '#241D12', 'bg' => '#FBF8F1', 'bgAlt' => '#F1E9D8',
                    'radius' => '1rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Figtree', 'display' => 'Lora', 'arabic' => 'Scheherazade New'],
                'layout' => 'klasik-formal',
                'icon_style' => ['weight' => 'halus', 'stroke_width' => 1.25, 'container' => 'tanpa-bekas'],
                'preview_meta' => ['suitable_for' => 'Masjid bersejarah/klasik'],
            ],
            [
                'key' => 'teal_kontemporari',
                'name' => 'Teal Kontemporari',
                'tokens' => [
                    'primary' => '#0F6E6E', 'primaryDark' => '#084C4C', 'accent' => '#E0A94F',
                    'ink' => '#10201F', 'bg' => '#F6FBFA', 'bgAlt' => '#E3F0EE',
                    'radius' => '1rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'IBM Plex Sans', 'display' => 'IBM Plex Serif', 'arabic' => 'Amiri'],
                'layout' => 'grid-kad',
                'icon_style' => ['weight' => 'tebal', 'stroke_width' => 2.25, 'container' => 'bulat-penuh'],
                'preview_meta' => ['suitable_for' => 'Komuniti muda/mesra keluarga'],
            ],
            [
                'key' => 'marun_agung',
                'name' => 'Marun Agung',
                'tokens' => [
                    'primary' => '#6E1F2E', 'primaryDark' => '#4A121D', 'accent' => '#C9A961',
                    'ink' => '#1D1416', 'bg' => '#FAF6F4', 'bgAlt' => '#F0E6E4',
                    'radius' => '1rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Plus Jakarta Sans', 'display' => 'Cormorant Garamond', 'arabic' => 'Amiri'],
                'layout' => 'klasik-formal',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'bulat-penuh'],
                'preview_meta' => ['suitable_for' => 'Masjid besar/kerajaan'],
            ],
        ];

        foreach ($packages as $package) {
            DesignPackage::updateOrCreate(['key' => $package['key']], $package + ['is_active' => true]);
        }
    }
}
