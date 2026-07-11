<?php

namespace Database\Seeders;

use App\Models\TemplateCatalog;
use Illuminate\Database\Seeder;

/**
 * §Fasa 16 — katalog templat rujukan terkurasi (galeri wizard mod 'template').
 * URL disahkan sebenar (carian web, Julai 2026). Idempoten (updateOrCreate ikut url).
 * Thumbnail null — admin tangkap screenshot & muat naik sendiri (elak salin aset berhak cipta pukal).
 */
class TemplateCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // ── Masjid / Islamic (ThemeForest — item sebenar) ─────────────────────
            [
                'name' => 'Muezzin — Islamic Center & Mosque',
                'url' => 'https://themeforest.net/item/muezzin-islamic-center-mosque-wordpress-theme/50071539',
                'categories' => ['masjid'], 'style_tags' => ['moden', 'lengkap', 'waktu-solat'], 'sort' => 1,
                'description' => 'Islamic center & masjid — khutbah, kempen derma, jadual solat, kalendar, acara. Rujukan struktur lengkap.',
            ],
            [
                'name' => 'Alquran — Islamic Institute & Mosque',
                'url' => 'https://themeforest.net/item/alquran-islamic-institute-mosque-wordpress-theme/50484102',
                'categories' => ['masjid'], 'style_tags' => ['elegan', 'institut', 'pembelajaran'], 'sort' => 2,
                'description' => 'Institut Islam & masjid — gabungan keanggunan dan fungsi untuk pusat pembelajaran.',
            ],
            [
                'name' => 'Tabligh — Islamic Institute & Mosque',
                'url' => 'https://themeforest.net/item/tabligh-islamic-institute-mosque-wordpress-theme/29880812',
                'categories' => ['masjid'], 'style_tags' => ['klasik', 'kursus', 'donation'], 'sort' => 3,
                'description' => 'Kursus dalam talian, khutbah audio, derma, waktu solat, ulama & acara.',
            ],

            // ── Masjid (ThemeForest — halaman browse; PIC boleh terokai banyak pilihan) ──
            [
                'name' => 'Semua Tema Masjid (ThemeForest — browse)',
                'url' => 'https://themeforest.net/category/wordpress?term=mosque',
                'categories' => ['masjid'], 'style_tags' => ['browse', 'pelbagai'], 'sort' => 10,
                'description' => 'Layari puluhan tema masjid ThemeForest. Beritahu kami mana yang anda suka melalui nota.',
            ],
            [
                'name' => 'Tema Islamic Center (ThemeForest — browse)',
                'url' => 'https://themeforest.net/category/wordpress?term=islamic%20center',
                'categories' => ['masjid'], 'style_tags' => ['browse', 'pelbagai'], 'sort' => 11,
                'description' => 'Koleksi tema pusat Islam / masjid ThemeForest untuk rujukan gaya.',
            ],
            [
                'name' => 'Tema Masjid (ThemeForest — browse "masjid")',
                'url' => 'https://themeforest.net/category/wordpress?term=masjid',
                'categories' => ['masjid'], 'style_tags' => ['browse', 'pelbagai'], 'sort' => 12,
                'description' => 'Carian "masjid" di ThemeForest.',
            ],

            // ── Laman masjid Malaysia sebenar (inspirasi tempatan) ────────────────
            [
                'name' => 'Masjid Al-Muttaqin Wangsa Melawati (mamkl.my)',
                'url' => 'https://www.mamkl.my', 'source' => 'laman',
                'categories' => ['masjid'], 'style_tags' => ['premium', 'moden', 'tempatan', 'gold-standard'], 'sort' => 20,
                'description' => 'Contoh laman masjid premium tempatan — aras kualiti yang kami sasarkan.',
            ],
            [
                'name' => 'Masjid Wilayah Persekutuan (masjidwilayah.gov.my)',
                'url' => 'https://www.masjidwilayah.gov.my', 'source' => 'laman',
                'categories' => ['masjid'], 'style_tags' => ['rasmi', 'tempatan'], 'sort' => 21,
                'description' => 'Laman rasmi masjid — rujukan struktur maklumat & waktu solat.',
            ],
            [
                'name' => 'Masjid Negara (masjidnegara.gov.my)',
                'url' => 'https://www.masjidnegara.gov.my', 'source' => 'laman',
                'categories' => ['masjid'], 'style_tags' => ['rasmi', 'tempatan'], 'sort' => 22,
                'description' => 'Laman rasmi Masjid Negara — rujukan tempatan.',
            ],

            // ── NGO / Charity (ThemeForest — item sebenar) ────────────────────────
            [
                'name' => 'Alone — Charity Multipurpose Non-profit',
                'url' => 'https://themeforest.net/item/alone-charity-multipurpose-nonprofit-wordpress-theme/15019939',
                'categories' => ['ngo'], 'style_tags' => ['terlaris', 'donation', 'pelbagai-guna'], 'sort' => 30,
                'description' => 'Tema amal terlaris ThemeForest — 40+ demo, integrasi derma penuh. Rujukan NGO yang matang.',
            ],
            [
                'name' => 'Charity NGO — Donation & Nonprofit',
                'url' => 'https://themeforest.net/item/charity-ngo-donation-nonprofit-ngo-charity-wordpress-theme/19582809',
                'categories' => ['ngo'], 'style_tags' => ['donation', 'acara', 'moden'], 'sort' => 31,
                'description' => 'Sistem derma & pengurusan acara lengkap untuk NGO / pertubuhan.',
            ],

            // ── NGO (ThemeForest — halaman browse) ────────────────────────────────
            [
                'name' => 'Semua Tema NGO / Amal (ThemeForest — browse)',
                'url' => 'https://themeforest.net/category/wordpress/nonprofit',
                'categories' => ['ngo'], 'style_tags' => ['browse', 'pelbagai'], 'sort' => 40,
                'description' => 'Layari 400+ tema NGO & bukan-untung ThemeForest.',
            ],
            [
                'name' => 'Tema Charity (ThemeForest — browse)',
                'url' => 'https://themeforest.net/category/wordpress/nonprofit/charity',
                'categories' => ['ngo'], 'style_tags' => ['browse', 'donation'], 'sort' => 41,
                'description' => 'Koleksi tema amal ThemeForest untuk rujukan gaya.',
            ],
            [
                'name' => 'Tema NGO (ThemeForest — browse "ngo")',
                'url' => 'https://themeforest.net/category/wordpress/nonprofit?term=ngo',
                'categories' => ['ngo'], 'style_tags' => ['browse', 'pelbagai'], 'sort' => 42,
                'description' => 'Carian "ngo" dalam kategori bukan-untung ThemeForest.',
            ],
        ];

        foreach ($rows as $row) {
            TemplateCatalog::updateOrCreate(
                ['url' => $row['url']],
                array_merge([
                    'source' => 'themeforest',
                    'demo_url' => null,
                    'thumbnail_path' => null,
                    'screenshots' => null,
                    'is_active' => true,
                ], $row),
            );
        }
    }
}
