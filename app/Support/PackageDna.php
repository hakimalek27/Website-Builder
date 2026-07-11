<?php

namespace App\Support;

/**
 * §Fasa 15 — "DNA reka bentuk" naratif per pakej. Diberi kepada jurutera prompt (P1)
 * supaya arahan estetik konkrit (bukan sekadar kata kunci enum) memandu penjana HTML.
 * Setiap perenggan = watak visual: mood, olahan hero, penggunaan aksen, irama ruang.
 * Bukan fakta organisasi — arahan gaya sahaja.
 */
class PackageDna
{
    private const DNA = [
        'warisan_hijau' => 'Warisan Islamik klasik yang dipercayai — hijau zamrud dalam dengan aksen emas halus. Hero lapang dan khusyuk, tipografi serif berwibawa, banyak ruang putih. Aksen emas hanya pada butiran (garis, eyebrow, ikon) — jangan berlebihan. Rasa tenang, mapan, dan berakar tradisi. Bayang lembut, kad bersih, corak geometri amat halus di latar.',
        'biru_nilam' => 'Masjid bandar moden dan segar — biru nilam yakin dengan emas tembaga. Hero berbelah (teks + visual) bergaya editorial, grid kemas, garis tajam. Nada profesional dan bersih seperti institusi kontemporari. Guna kontras biru gelap lawan latar cerah, kad dengan bayang sederhana, ruang bernafas.',
        'emas_kubah' => 'Kemegahan bersejarah — emas kubah hangat dengan aksen hijau. Hero formal berbingkai, tipografi serif klasik, ornamen emas lebih menonjol (garis berlian, pembatas). Rasa berusia, dihormati, seperti masjid warisan. Latar krim hangat, butiran keemasan pada tajuk dan pembatas seksyen.',
        'teal_kontemporari' => 'Komuniti muda bertenaga — teal segar dengan aksen jingga keemasan. Susun atur grid-kad dinamik, ikon tebal bulat penuh, banyak kad interaktif dengan hover-lift jelas. Nada mesra, aktif, moden. Warna berani tetapi seimbang; gunakan ruang dan bayang untuk kedalaman.',
        'marun_agung' => 'Keagungan rasmi — marun dalam dengan emas diraja. Hero klasik-formal berpusat, tipografi serif besar, aura upacara dan kerajaan. Sesuai masjid besar/negeri. Aksen emas berwibawa pada tajuk; kad kemas berbingkai; irama seksyen yang tenang dan berwibawa.',
        'safa_putih' => 'Minimalis suci — hampir putih dengan hijau lembut dan emas nipis. Hero penuh lapang, tipografi bersih, garis halus. Estetik "less is more" — biar ruang putih dan tipografi bercakap. Bayang sangat halus, pembatas garis emas nipis, tiada kekacauan visual.',
        'nilam_senja' => 'Dramatik senja — ungu nilam dalam dengan jingga hangat. Hero berbelah bermood, gradien kaya, kad terapung berbayang dalam, ikon heksagon. Nada emosional, kontemporari, berani. Guna latar gelap berselang-seli untuk drama; corak geometri halus menambah tekstur.',
        'zaitun_tenang' => 'Mesra alam dan tenang — hijau zaitun lembut dengan emas tanah. Hero tengah damai, kad lembut, pembatas garis emas. Nada organik, hangat, membumi. Ruang lapang, tipografi mesra, aksen semula jadi tanpa kilauan berlebihan.',
        'pasir_gurun' => 'Tradisional padang pasir — coklat pasir hangat dengan teal oasis. Hero klasik berbingkai, tipografi serif kemas, ikon kotak tegas. Nada berusia, jujur, tekstur tanah. Latar krim pasir, pembatas lengkung lembut, corak arabesque halus.',
        'langit_subuh' => 'Mesra keluarga cerah — biru langit lembut dengan merah jambu subuh. Susun atur grid-kad ringan, kad bulat mesra, banyak ruang lapang terang. Nada mengalu-alukan, lembut, ceria tanpa kekanak-kanakan. Bayang lembut dan warna pastel seimbang.',
        'arang_moden' => 'Berkelas dan dramatik — arang gelap dengan emas berkilau. Hero gelap bertekstur (foto/gradien dalam), tipografi kontras tinggi, ruang putih luas, kad terapung dalam. Estetik butik mewah/premium. Aksen emas menyerlah lawan latar arang; guna seksyen berlatar gelap untuk kesan teater.',
        'akar_komuniti' => 'NGO komuniti membumi — jingga bata hangat dengan hijau harapan. Hero grid-kad mesra rakyat, ikon bulat penuh tebal, nada bertenaga dan merakyat. Fokus manusia dan aktiviti; kad program menonjol; warna hangat mengundang penglibatan.',
        'amanah_biru' => 'Yayasan amanah korporat — biru dalam dengan emas amanah. Hero berbelah profesional, grid kemas, tipografi berwibawa, nada telus dan dipercayai. Sesuai yayasan/dana. Struktur jelas, kad bersih bergaris, aksen emas pada angka dan pencapaian.',
        'harapan_hijau' => 'Kebajikan penuh harapan — hijau cerah dengan emas keprihatinan. Hero penuh bertenaga, kad terapung, pembatas lengkung, nada optimistik dan menggerakkan derma. Tekankan impak dan CTA derma; guna gradien hijau segar dan ruang lapang yang positif.',
    ];

    /** Perenggan DNA seni untuk pakej, atau fallback am. */
    public static function for(string $packageKey): string
    {
        return self::DNA[$packageKey] ?? self::DNA['warisan_hijau'];
    }

    /** @return array<string,string> */
    public static function all(): array
    {
        return self::DNA;
    }
}
