<?php

namespace Database\Seeders;

use App\Models\JakimZone;
use Illuminate\Database\Seeder;

/**
 * 59 zon JAKIM (§16.A). Kod = KRITIKAL (padanan e-Solat); label = paparan UI,
 * selaras portal rasmi e-solat.gov.my (BUKAN repo acfatah yang lapuk).
 * WAJIB jalankan `php artisan zones:verify` selepas seed pertama di produksi.
 */
class JakimZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            // Johor (4)
            ['JHR01', 'Johor', 'Pulau Aur, Pulau Pemanggil'],
            ['JHR02', 'Johor', 'Johor Bahru, Kota Tinggi, Mersing, Kulai'],
            ['JHR03', 'Johor', 'Kluang, Pontian'],
            ['JHR04', 'Johor', 'Batu Pahat, Muar, Segamat, Gemas Johor'],

            // Kedah (7)
            ['KDH01', 'Kedah', 'Kota Setar, Kubang Pasu, Pokok Sena'],
            ['KDH02', 'Kedah', 'Kuala Muda, Yan, Pendang'],
            ['KDH03', 'Kedah', 'Padang Terap, Sik'],
            ['KDH04', 'Kedah', 'Baling'],
            ['KDH05', 'Kedah', 'Bandar Baharu, Kulim'],
            ['KDH06', 'Kedah', 'Langkawi'],
            ['KDH07', 'Kedah', 'Puncak Gunung Jerai'],

            // Kelantan (2) — TIADA KTN02
            ['KTN01', 'Kelantan', 'Bachok, Kota Bharu, Machang, Pasir Mas, Pasir Puteh, Tanah Merah, Tumpat, Kuala Krai, Mukim Chiku'],
            ['KTN03', 'Kelantan', 'Gua Musang (Galas & Bertam), Jeli, Jajahan Lojing'],

            // Melaka (1)
            ['MLK01', 'Melaka', 'Seluruh Negeri Melaka'],

            // Negeri Sembilan (3)
            ['NGS01', 'Negeri Sembilan', 'Tampin, Jempol'],
            ['NGS02', 'Negeri Sembilan', 'Jelebu, Kuala Pilah, Rembau'],
            ['NGS03', 'Negeri Sembilan', 'Port Dickson, Seremban'],

            // Pahang (6)
            ['PHG01', 'Pahang', 'Pulau Tioman'],
            ['PHG02', 'Pahang', 'Kuantan, Pekan, Muadzam Shah'],
            ['PHG03', 'Pahang', 'Jerantut, Temerloh, Maran, Bera, Chenor, Jengka'],
            ['PHG04', 'Pahang', 'Bentong, Lipis, Raub'],
            ['PHG05', 'Pahang', 'Genting Sempah, Janda Baik, Bukit Tinggi'],
            ['PHG06', 'Pahang', 'Cameron Highlands, Genting Highlands, Bukit Fraser'],

            // Perlis (1)
            ['PLS01', 'Perlis', 'Seluruh Negeri Perlis'],

            // Pulau Pinang (1)
            ['PNG01', 'Pulau Pinang', 'Seluruh Negeri Pulau Pinang'],

            // Perak (7)
            ['PRK01', 'Perak', 'Tapah, Slim River, Tanjung Malim'],
            ['PRK02', 'Perak', 'Kuala Kangsar, Sg. Siput, Ipoh, Batu Gajah, Kampar'],
            ['PRK03', 'Perak', 'Lenggong, Pengkalan Hulu, Grik'],
            ['PRK04', 'Perak', 'Temengor, Belum'],
            ['PRK05', 'Perak', 'Kg. Gajah, Teluk Intan, Bagan Datuk, Seri Iskandar, Beruas, Parit, Lumut, Sitiawan, Pulau Pangkor'],
            ['PRK06', 'Perak', 'Selama, Taiping, Bagan Serai, Parit Buntar'],
            ['PRK07', 'Perak', 'Bukit Larut'],

            // Sabah (9)
            ['SBH01', 'Sabah', 'Bhg. Sandakan (Timur): Bandar Sandakan, Bukit Garam, Semawang, Temanggong, Tambisan, Sukau'],
            ['SBH02', 'Sabah', 'Bhg. Sandakan (Barat): Beluran, Telupid, Pinangah, Terusan, Kuamut'],
            ['SBH03', 'Sabah', 'Bhg. Tawau (Timur): Lahad Datu, Silabukan, Kunak, Sahabat, Semporna, Tungku'],
            ['SBH04', 'Sabah', 'Bhg. Tawau (Barat): Bandar Tawau, Balong, Merotai, Kalabakan'],
            ['SBH05', 'Sabah', 'Bhg. Kudat: Kudat, Kota Marudu, Pitas, Pulau Banggi'],
            ['SBH06', 'Sabah', 'Gunung Kinabalu'],
            ['SBH07', 'Sabah', 'Bhg. Pantai Barat: Kota Kinabalu, Ranau, Kota Belud, Tuaran, Penampang, Papar, Putatan'],
            ['SBH08', 'Sabah', 'Bhg. Pedalaman (Atas): Pensiangan, Keningau, Tambunan, Nabawan'],
            ['SBH09', 'Sabah', 'Bhg. Pedalaman (Bawah): Beaufort, Kuala Penyu, Sipitang, Tenom, Long Pasia, Membakut, Weston'],

            // Sarawak (9)
            ['SWK01', 'Sarawak', 'Limbang, Lawas, Sundar, Trusan'],
            ['SWK02', 'Sarawak', 'Miri, Niah, Bekenu, Sibuti, Marudi'],
            ['SWK03', 'Sarawak', 'Pandan, Belaga, Suai, Tatau, Sebauh, Bintulu'],
            ['SWK04', 'Sarawak', 'Sibu, Mukah, Dalat, Song, Igan, Oya, Balingian, Kanowit, Kapit'],
            ['SWK05', 'Sarawak', 'Sarikei, Matu, Julau, Rajang, Daro, Bintangor, Belawai'],
            ['SWK06', 'Sarawak', 'Lubok Antu, Sri Aman, Roban, Debak, Kabong, Lingga, Engkelili, Betong, Spaoh, Pusa, Saratok'],
            ['SWK07', 'Sarawak', 'Serian, Simunjan, Samarahan, Sebuyau, Meludam'],
            ['SWK08', 'Sarawak', 'Kuching, Bau, Lundu, Sematan'],
            ['SWK09', 'Sarawak', 'Zon Khas (Kg. Patarikan)'],

            // Selangor (3)
            ['SGR01', 'Selangor', 'Gombak, Petaling, Sepang, Hulu Langat, Hulu Selangor, Shah Alam'],
            ['SGR02', 'Selangor', 'Kuala Selangor, Sabak Bernam'],
            ['SGR03', 'Selangor', 'Klang, Kuala Langat'],

            // Terengganu (4)
            ['TRG01', 'Terengganu', 'Kuala Terengganu, Marang, Kuala Nerus'],
            ['TRG02', 'Terengganu', 'Besut, Setiu'],
            ['TRG03', 'Terengganu', 'Hulu Terengganu'],
            ['TRG04', 'Terengganu', 'Dungun, Kemaman'],

            // Wilayah Persekutuan (2)
            ['WLY01', 'Wilayah Persekutuan', 'Kuala Lumpur, Putrajaya'],
            ['WLY02', 'Wilayah Persekutuan', 'Labuan'],
        ];

        foreach ($zones as [$code, $state, $label]) {
            JakimZone::updateOrCreate(
                ['code' => $code],
                ['state' => $state, 'districts_label' => $label],
            );
        }
    }
}
