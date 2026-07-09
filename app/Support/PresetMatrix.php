<?php

namespace App\Support;

use App\Enums\Tier;

/**
 * Matriks preset tier (§6.11) — nilai awal project_pages.
 * utama & hubungi kekal wajib (tidak boleh nyah-tanda).
 */
class PresetMatrix
{
    /** Halaman wajib pada semua tier (tidak boleh dinyah-tanda). */
    public const MANDATORY = ['utama', 'hubungi'];

    /**
     * page_key yang DITANDA (enabled) untuk tier + is_gov (§6.11).
     *
     * @return array<int, string>
     */
    public static function pagesFor(Tier $tier, bool $isGov): array
    {
        // Base — semua tier.
        $base = [
            'utama', 'waktu_solat', 'hubungi',
            'pengumuman', 'infaq', 'soalan_lazim',
            'kuliah_mingguan',
        ];

        $kariah = [
            'sejarah', 'ajk', 'fasiliti', 'galeri', 'berita',
            'kelas_quran', 'nikah', 'jenazah', 'tahlil_doa', 'khairat',
            'khutbah_jumaat', 'program_akan_datang', 'muat_turun',
        ];

        $besar = [
            'perutusan', 'visi_misi', 'direktori_pegawai',
            'sewa_dewan', 'info_pelawat', 'live_streaming',
        ];

        $pages = $base;

        if ($tier === Tier::MasjidKariah || $tier === Tier::MasjidBesar) {
            $pages = array_merge($pages, $kariah);
        }

        if ($tier === Tier::MasjidBesar) {
            $pages = array_merge($pages, $besar);
        }

        // is_gov paksa: perutusan, visi_misi, direktori_pegawai (mana-mana tier).
        if ($isGov) {
            $pages = array_merge($pages, ['perutusan', 'visi_misi', 'direktori_pegawai']);
        }

        return array_values(array_unique($pages));
    }
}
