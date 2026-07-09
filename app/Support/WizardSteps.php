<?php

namespace App\Support;

/**
 * Definisi 10 langkah wizard (§6). section_key = "step_{n}".
 */
class WizardSteps
{
    /**
     * @return array<int, array{index: int, title: string, subtitle: string}>
     */
    public static function all(): array
    {
        return [
            ['index' => 0, 'title' => 'Jenis Masjid & Titik Mula', 'subtitle' => 'Tier & pematuhan kerajaan'],
            ['index' => 1, 'title' => 'Maklumat Asas Masjid', 'subtitle' => 'Nama, alamat, hubungi, zon solat'],
            ['index' => 2, 'title' => 'Identiti & Reka Bentuk', 'subtitle' => 'Pakej warna, font, ikon, susun atur'],
            ['index' => 3, 'title' => 'Struktur Halaman', 'subtitle' => 'Pilih halaman untuk laman anda'],
            ['index' => 4, 'title' => 'Kandungan Halaman', 'subtitle' => 'Isi butiran setiap halaman'],
            ['index' => 5, 'title' => 'Fungsi & Ciri', 'subtitle' => 'Pembayaran, WhatsApp, CMS'],
            ['index' => 6, 'title' => 'Media & Aset', 'subtitle' => 'Imej hero, logo, galeri'],
            ['index' => 7, 'title' => 'Rujukan & Inspirasi', 'subtitle' => 'Laman yang anda suka'],
            ['index' => 8, 'title' => 'Teknikal & Operasi', 'subtitle' => 'Domain, hosting, penyelenggaraan'],
            ['index' => 9, 'title' => 'Nota, Perakuan & Persetujuan', 'subtitle' => 'Pengesahan akhir'],
        ];
    }

    public static function sectionKey(int $index): string
    {
        return "step_{$index}";
    }

    public static function count(): int
    {
        return count(self::all());
    }
}
