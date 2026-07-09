<?php

namespace Database\Seeders;

use App\Models\DesignPackage;
use Illuminate\Database\Seeder;

/**
 * 14 pakej reka bentuk (§7.2 + pelbagaian Fasa 11). Tokens hex TEPAT & disahkan WCAG
 * (primary boleh-baca atas bg + teks putih ≥ 4.5:1 — lihat DesignPaletteContrastTest).
 * icon_style: berat garisan (halus 1.25 / sederhana 1.75 / tebal 2.25) + bekas.
 * variants: header/footer/card/divider (§7 pelbagaian struktur). 5 pakej asal kekal
 * tanpa variants (default shell = rupa produksi terbukti). preview_meta.org: masjid|ngo|semua.
 */
class DesignPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // ── 5 pakej asal (§7.2) — variants null → shell default (byte-identik) ──
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
                'preview_meta' => ['suitable_for' => 'Semua — token terbukti produksi mamkl.my', 'org' => 'semua'],
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
                'preview_meta' => ['suitable_for' => 'Masjid bandar moden', 'org' => 'semua'],
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
                'preview_meta' => ['suitable_for' => 'Masjid bersejarah/klasik', 'org' => 'semua'],
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
                'preview_meta' => ['suitable_for' => 'Komuniti muda/mesra keluarga', 'org' => 'semua'],
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
                'preview_meta' => ['suitable_for' => 'Masjid besar/kerajaan', 'org' => 'semua'],
            ],

            // ── 9 pakej baharu (Fasa 11) — variants pelbagai + layout baharu ──
            [
                'key' => 'safa_putih',
                'name' => 'Safa Putih',
                'tokens' => [
                    'primary' => '#2F6E4F', 'primaryDark' => '#1E4A34', 'accent' => '#B8892F',
                    'ink' => '#1A211D', 'bg' => '#FFFFFF', 'bgAlt' => '#F1F5F2',
                    'radius' => '0.75rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Manrope', 'display' => 'Marcellus', 'arabic' => 'Amiri'],
                'layout' => 'hero-penuh',
                'icon_style' => ['weight' => 'halus', 'stroke_width' => 1.5, 'container' => 'garis'],
                'variants' => ['header' => 'padat', 'footer' => 'ringkas', 'card' => 'garis', 'divider' => 'garis-emas'],
                'preview_meta' => ['suitable_for' => 'Minimalis bersih & lapang', 'org' => 'semua'],
            ],
            [
                'key' => 'nilam_senja',
                'name' => 'Nilam Senja',
                'tokens' => [
                    'primary' => '#43397D', 'primaryDark' => '#2A2352', 'accent' => '#D98E5A',
                    'ink' => '#1C1A2B', 'bg' => '#F8F6FC', 'bgAlt' => '#ECE7F5',
                    'radius' => '1.25rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Albert Sans', 'display' => 'Zilla Slab', 'arabic' => 'Amiri'],
                'layout' => 'hero-belah',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'heksagon'],
                'variants' => ['header' => 'gradien', 'footer' => 'tiga-lajur', 'card' => 'terapung', 'divider' => 'lengkung'],
                'preview_meta' => ['suitable_for' => 'Kontemporari dramatik', 'org' => 'semua'],
            ],
            [
                'key' => 'zaitun_tenang',
                'name' => 'Zaitun Tenang',
                'tokens' => [
                    'primary' => '#566A2C', 'primaryDark' => '#3A471C', 'accent' => '#A67C34',
                    'ink' => '#20231A', 'bg' => '#FAFBF4', 'bgAlt' => '#EEF2E1',
                    'radius' => '1rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Work Sans', 'display' => 'DM Serif Display', 'arabic' => 'Scheherazade New'],
                'layout' => 'hero-tengah',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'bulat-cair'],
                'variants' => ['header' => 'padat', 'footer' => 'tengah-jenama', 'card' => 'lembut', 'divider' => 'garis-emas'],
                'preview_meta' => ['suitable_for' => 'Semula jadi & mesra alam', 'org' => 'semua'],
            ],
            [
                'key' => 'pasir_gurun',
                'name' => 'Pasir Gurun',
                'tokens' => [
                    'primary' => '#8A5A2B', 'primaryDark' => '#5E3D1C', 'accent' => '#3E7A6E',
                    'ink' => '#241C13', 'bg' => '#FBF7F0', 'bgAlt' => '#F0E7D7',
                    'radius' => '0.5rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Source Sans 3', 'display' => 'Source Serif 4', 'arabic' => 'Amiri'],
                'layout' => 'klasik-formal',
                'icon_style' => ['weight' => 'halus', 'stroke_width' => 1.5, 'container' => 'kotak-tegas'],
                'variants' => ['header' => 'tengah', 'footer' => 'tiga-lajur', 'card' => 'garis', 'divider' => 'lengkung'],
                'preview_meta' => ['suitable_for' => 'Hangat & tradisional', 'org' => 'semua'],
            ],
            [
                'key' => 'langit_subuh',
                'name' => 'Langit Subuh',
                'tokens' => [
                    'primary' => '#2C6389', 'primaryDark' => '#1B415C', 'accent' => '#C56F86',
                    'ink' => '#16232B', 'bg' => '#F6FAFD', 'bgAlt' => '#E5EFF6',
                    'radius' => '1.25rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Nunito Sans', 'display' => 'Fraunces', 'arabic' => 'Amiri'],
                'layout' => 'grid-kad',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'bulat-penuh'],
                'variants' => ['header' => 'gradien', 'footer' => 'ringkas', 'card' => 'terapung', 'divider' => 'tiada'],
                'preview_meta' => ['suitable_for' => 'Lembut & mesra keluarga', 'org' => 'semua'],
            ],
            [
                'key' => 'arang_moden',
                'name' => 'Arang Moden',
                'tokens' => [
                    'primary' => '#232E38', 'primaryDark' => '#10171C', 'accent' => '#C9A961',
                    'ink' => '#12181D', 'bg' => '#F4F5F6', 'bgAlt' => '#E3E6E9',
                    'radius' => '0.75rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Inter', 'display' => 'Playfair Display', 'arabic' => 'Amiri'],
                'layout' => 'hero-mihrab',
                'icon_style' => ['weight' => 'tebal', 'stroke_width' => 2.0, 'container' => 'kotak-tegas'],
                'variants' => ['header' => 'padat', 'footer' => 'tengah-jenama', 'card' => 'terapung', 'divider' => 'garis-emas'],
                'preview_meta' => ['suitable_for' => 'Berkelas & berwibawa', 'org' => 'semua'],
            ],
            [
                'key' => 'akar_komuniti',
                'name' => 'Akar Komuniti',
                'tokens' => [
                    'primary' => '#9A4526', 'primaryDark' => '#6A2F19', 'accent' => '#2F6E4F',
                    'ink' => '#241611', 'bg' => '#FBF6F3', 'bgAlt' => '#F1E3DB',
                    'radius' => '1rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Rubik', 'display' => 'Bitter', 'arabic' => 'Amiri'],
                'layout' => 'grid-kad',
                'icon_style' => ['weight' => 'tebal', 'stroke_width' => 2.0, 'container' => 'bulat-penuh'],
                'variants' => ['header' => 'tengah', 'footer' => 'tiga-lajur', 'card' => 'lembut', 'divider' => 'garis-emas'],
                'preview_meta' => ['suitable_for' => 'NGO komuniti & kebajikan', 'org' => 'ngo'],
            ],
            [
                'key' => 'amanah_biru',
                'name' => 'Amanah Biru',
                'tokens' => [
                    'primary' => '#17457A', 'primaryDark' => '#0E2E54', 'accent' => '#C99A3E',
                    'ink' => '#16202B', 'bg' => '#F7FAFC', 'bgAlt' => '#E7EEF4',
                    'radius' => '0.75rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Figtree', 'display' => 'Lora', 'arabic' => 'Amiri'],
                'layout' => 'hero-belah',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'kotak-lembut'],
                'variants' => ['header' => 'gradien', 'footer' => 'tiga-lajur', 'card' => 'garis', 'divider' => 'tiada'],
                'preview_meta' => ['suitable_for' => 'Yayasan & pertubuhan korporat', 'org' => 'ngo'],
            ],
            [
                'key' => 'harapan_hijau',
                'name' => 'Harapan Hijau',
                'tokens' => [
                    'primary' => '#1B6B40', 'primaryDark' => '#12492B', 'accent' => '#E0A94F',
                    'ink' => '#17241C', 'bg' => '#F5FBF7', 'bgAlt' => '#E3F1E9',
                    'radius' => '1.25rem', 'headerStyle' => 'transparent-to-solid',
                ],
                'fonts' => ['body' => 'Nunito Sans', 'display' => 'Fraunces', 'arabic' => 'Amiri'],
                'layout' => 'hero-penuh',
                'icon_style' => ['weight' => 'sederhana', 'stroke_width' => 1.75, 'container' => 'bulat-cair'],
                'variants' => ['header' => 'gradien', 'footer' => 'tengah-jenama', 'card' => 'terapung', 'divider' => 'lengkung'],
                'preview_meta' => ['suitable_for' => 'NGO kebajikan & dana', 'org' => 'ngo'],
            ],
        ];

        foreach ($packages as $package) {
            DesignPackage::updateOrCreate(['key' => $package['key']], $package + ['is_active' => true]);
        }
    }
}
