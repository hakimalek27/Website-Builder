<?php

namespace App\Support;

use App\Enums\Tier;

/**
 * Katalog page_key (§6 L3) dikumpul kluster + skema panel L4 (§6 L4).
 * Masjid & NGO (Fasa 11): clustersFor()/metaFor()/panelsFor() parameter ikut tier;
 * clusters()/panels() masjid kekal verbatim (laluan masjid byte-identik).
 *
 * Skema panel = deklaratif; satu renderer generik (livewire/wizard/panels/_field)
 * merender semua jenis medan. Ini menggantikan puluhan blade tangan.
 */
class PageCatalog
{
    /** Enum peringkat kelas Quran DIKUNCI dari skema mamkl (§6 L4). */
    public const QURAN_LEVELS = ['tahsin', 'hafazan', 'tadabbur', 'dhuha', 'tajwid', 'ulum', 'qiraat'];

    /** Senarai ikon infaq TERTUTUP dari skema mamkl (§6 L4). */
    public const INFAQ_ICONS = ['HeartHandshake', 'HandHeart', 'Building', 'Users', 'BookOpen', 'Sparkles'];

    /** Kategori FAQ enum mamkl (§6 L4). */
    public const FAQ_CATEGORIES = ['umum', 'pernikahan', 'jenazah', 'dewan', 'kelas', 'infaq'];

    public const MANDATORY = ['utama', 'hubungi'];

    /**
     * Kluster → senarai page_key (§6 L3). utama = wajib berasingan.
     *
     * @return array<string, array<int, string>>
     */
    public static function clusters(): array
    {
        return [
            'Korporat' => ['sejarah', 'perutusan', 'visi_misi', 'ajk', 'direktori_pegawai'],
            'Ibadah' => ['waktu_solat', 'khutbah_jumaat', 'live_streaming', 'kiblat'],
            'Ilmu' => ['kuliah_mingguan', 'kuliah_bulanan_poster', 'kelas_quran', 'kafa'],
            'Aktiviti' => ['berita', 'pengumuman', 'program_akan_datang', 'galeri'],
            'Kariah' => ['nikah', 'jenazah', 'tahlil_doa', 'khidmat_nasihat', 'khairat', 'daftar_kariah_link'],
            'Fasiliti' => ['fasiliti', 'sewa_dewan', 'info_pelawat'],
            'Kewangan' => ['infaq'],
            'Sokongan' => ['soalan_lazim', 'hubungi'],
            'Muat turun' => ['muat_turun'],
        ];
    }

    /**
     * Meta setiap page_key: label BM + tooltip (1 ayat + "Dilihat di").
     *
     * @return array<string, array{label: string, tooltip: string}>
     */
    public static function meta(): array
    {
        return [
            'utama' => ['label' => 'Halaman Utama', 'tooltip' => 'Muka depan laman anda. Dilihat di: semua masjid.'],
            'sejarah' => ['label' => 'Sejarah', 'tooltip' => 'Latar belakang & penubuhan masjid. Dilihat di: mamkl.my.'],
            'perutusan' => ['label' => 'Perutusan', 'tooltip' => 'Kata alu-aluan Nazir/Imam. Dilihat di: masjidwilayah.gov.my.'],
            'visi_misi' => ['label' => 'Visi & Misi', 'tooltip' => 'Hala tuju masjid. Dilihat di: masjid korporat.'],
            'ajk' => ['label' => 'AJK / Jawatankuasa', 'tooltip' => 'Senarai ahli jawatankuasa. Dilihat di: mamkl.my.'],
            'direktori_pegawai' => ['label' => 'Direktori Pegawai', 'tooltip' => 'Senarai pegawai & hubungan. Dilihat di: masjid besar.'],
            'waktu_solat' => ['label' => 'Waktu Solat', 'tooltip' => 'Waktu solat rasmi JAKIM. Dilihat di: semua masjid.'],
            'khutbah_jumaat' => ['label' => 'Khutbah Jumaat', 'tooltip' => 'Arkib/teks khutbah. Dilihat di: masjid besar.'],
            'live_streaming' => ['label' => 'Siaran Langsung', 'tooltip' => 'Live YouTube/Facebook. Dilihat di: masjid besar.'],
            'kiblat' => ['label' => 'Arah Kiblat', 'tooltip' => 'Arah kiblat automatik dari GPS. Dilihat di: masjid pelawat.'],
            'kuliah_mingguan' => ['label' => 'Kuliah Mingguan', 'tooltip' => 'Jadual kuliah mingguan. Dilihat di: mamkl.my.'],
            'kuliah_bulanan_poster' => ['label' => 'Kuliah Bulanan (Poster)', 'tooltip' => 'Poster program bulanan. Dilihat di: masjid kariah.'],
            'kelas_quran' => ['label' => 'Kelas Al-Quran', 'tooltip' => 'Kelas tahsin/hafazan dll. Dilihat di: mamkl.my.'],
            'kafa' => ['label' => 'KAFA', 'tooltip' => 'Kelas Fardhu Ain kanak-kanak. Dilihat di: masjid kariah.'],
            'berita' => ['label' => 'Berita', 'tooltip' => 'Berita & artikel masjid. Dilihat di: masjid kariah.'],
            'pengumuman' => ['label' => 'Pengumuman', 'tooltip' => 'Hebahan ringkas. Dilihat di: semua masjid.'],
            'program_akan_datang' => ['label' => 'Program Akan Datang', 'tooltip' => 'Kalendar acara. Dilihat di: masjid kariah.'],
            'galeri' => ['label' => 'Galeri', 'tooltip' => 'Koleksi gambar aktiviti. Dilihat di: mamkl.my.'],
            'nikah' => ['label' => 'Nikah', 'tooltip' => 'Khidmat & syarat pernikahan. Dilihat di: masjid kariah.'],
            'jenazah' => ['label' => 'Jenazah', 'tooltip' => 'Khidmat pengurusan jenazah. Dilihat di: masjid kariah.'],
            'tahlil_doa' => ['label' => 'Tahlil & Doa', 'tooltip' => 'Khidmat tahlil/doa. Dilihat di: masjid kariah.'],
            'khidmat_nasihat' => ['label' => 'Khidmat Nasihat', 'tooltip' => 'Kaunseling & nasihat. Dilihat di: masjid kariah.'],
            'khairat' => ['label' => 'Khairat Kematian', 'tooltip' => 'Skim khairat kematian. Dilihat di: masjid kariah.'],
            'daftar_kariah_link' => ['label' => 'Daftar Kariah (pautan)', 'tooltip' => 'Pautan ke sistem kariah. Dilihat di: mamkl.my.'],
            'fasiliti' => ['label' => 'Fasiliti', 'tooltip' => 'Kemudahan masjid. Dilihat di: mamkl.my.'],
            'sewa_dewan' => ['label' => 'Sewa Dewan', 'tooltip' => 'Tempahan & kadar dewan. Dilihat di: masjidwilayah.gov.my.'],
            'info_pelawat' => ['label' => 'Info Pelawat', 'tooltip' => 'Maklumat untuk pelawat. Dilihat di: Masjid Negara.'],
            'infaq' => ['label' => 'Infaq / Derma', 'tooltip' => 'Kategori infaq & maklumat bank. Dilihat di: mamkl.my.'],
            'soalan_lazim' => ['label' => 'Soalan Lazim (FAQ)', 'tooltip' => 'Soalan & jawapan biasa. Dilihat di: masjid besar.'],
            'hubungi' => ['label' => 'Hubungi Kami', 'tooltip' => 'Maklumat & borang hubungan. Dilihat di: semua masjid.'],
            'muat_turun' => ['label' => 'Muat Turun', 'tooltip' => 'Dokumen PDF untuk dimuat turun. Dilihat di: masjid kariah.'],
            // Halaman khusus NGO / pertubuhan (Fasa 11).
            'profil' => ['label' => 'Profil & Sejarah', 'tooltip' => 'Latar belakang, misi & penubuhan pertubuhan.'],
            'program_utama' => ['label' => 'Program & Inisiatif', 'tooltip' => 'Program utama & aktiviti pertubuhan.'],
            'sukarelawan' => ['label' => 'Sukarelawan', 'tooltip' => 'Peluang & cara menyertai sebagai sukarelawan.'],
            'keahlian' => ['label' => 'Keahlian', 'tooltip' => 'Syarat, yuran & manfaat menjadi ahli.'],
            'derma' => ['label' => 'Derma / Sumbangan', 'tooltip' => 'Kategori derma & maklumat bank/QR.'],
        ];
    }

    // ── Katalog ikut tier (masjid vs NGO) — Fasa 11 ──

    /** @return array<string, array<int, string>> */
    public static function clustersFor(Tier $tier): array
    {
        return $tier->isNgo() ? self::ngoClusters() : self::clusters();
    }

    /** @return array<string, array{label: string, tooltip: string}> */
    public static function metaFor(Tier $tier): array
    {
        $keys = array_merge(self::MANDATORY, ...array_values(self::clustersFor($tier)));

        return array_intersect_key(self::meta(), array_flip($keys));
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    public static function panelsFor(Tier $tier): array
    {
        return $tier->isNgo() ? self::ngoPanels() : self::panels();
    }

    /** Kluster NGO → page_key. utama & hubungi kekal wajib berasingan. */
    private static function ngoClusters(): array
    {
        return [
            'Korporat' => ['profil', 'perutusan', 'visi_misi', 'ajk'],
            'Program' => ['program_utama', 'program_akan_datang', 'berita', 'pengumuman', 'galeri'],
            'Penglibatan' => ['sukarelawan', 'keahlian', 'derma'],
            'Sokongan' => ['soalan_lazim', 'hubungi', 'muat_turun'],
        ];
    }

    /** page_key yang mempunyai panel kandungan L4. */
    public static function pagesWithPanel(): array
    {
        return array_keys(self::panels());
    }

    /**
     * Skema panel L4 setiap page_key (§6 L4).
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function panels(): array
    {
        $serviceTemplate = [
            ['key' => 'short_desc', 'type' => 'text', 'label' => 'Penerangan ringkas', 'required' => true, 'max' => 160],
            ['key' => 'full_desc', 'type' => 'textarea', 'label' => 'Penerangan penuh', 'ai' => true, 'max' => 1500],
            ['key' => 'requirements', 'type' => 'repeater_text', 'label' => 'Syarat', 'placeholder' => 'Cth: Sijil kelahiran'],
            ['key' => 'documents', 'type' => 'repeater_text', 'label' => 'Dokumen diperlukan', 'placeholder' => 'Cth: Salinan IC'],
            ['key' => 'fee', 'type' => 'text', 'label' => 'Bayaran', 'placeholder' => 'RM50 / percuma'],
            ['key' => 'apply_method', 'type' => 'textarea', 'label' => 'Cara memohon', 'required' => true, 'max' => 300, 'placeholder' => 'Hubungi pejabat / walk-in / borang'],
            ['key' => 'contact_person', 'type' => 'text', 'label' => 'Orang untuk dihubungi'],
        ];

        return [
            'sejarah' => [
                ['key' => 'mode', 'type' => 'radio', 'label' => 'Cara isi', 'required' => true, 'options' => [
                    'tulis_penuh' => 'Tulis penuh sendiri', 'butir_ringkas' => 'Butir ringkas (AI karang)', 'kemudian' => 'Beri kemudian',
                ]],
                ['key' => 'full_text', 'type' => 'textarea', 'label' => 'Teks sejarah', 'max' => 3000, 'showIf' => ['mode' => 'tulis_penuh']],
                ['key' => 'bullets', 'type' => 'repeater_text', 'label' => 'Butir ringkas', 'ai' => true, 'showIf' => ['mode' => 'butir_ringkas'], 'placeholder' => 'Cth: Ditubuhkan 1987 oleh penduduk kampung'],
                ['key' => 'milestones', 'type' => 'repeater', 'label' => 'Peristiwa penting', 'item' => [
                    ['key' => 'tahun', 'type' => 'text', 'label' => 'Tahun'], ['key' => 'peristiwa', 'type' => 'text', 'label' => 'Peristiwa'],
                ]],
                ['key' => 'former_leaders', 'type' => 'repeater', 'label' => 'Pemimpin terdahulu', 'item' => [
                    ['key' => 'nama', 'type' => 'text', 'label' => 'Nama'], ['key' => 'tempoh', 'type' => 'text', 'label' => 'Tempoh'],
                ]],
            ],
            'perutusan' => [
                ['key' => 'role', 'type' => 'select', 'label' => 'Jawatan', 'required' => true, 'options' => ['Nazir' => 'Nazir', 'Imam Besar' => 'Imam Besar', 'Pengerusi' => 'Pengerusi']],
                ['key' => 'name', 'type' => 'text', 'label' => 'Nama', 'required' => true],
                ['key' => 'photo', 'type' => 'upload', 'label' => 'Gambar'],
                ['key' => 'mode', 'type' => 'radio', 'label' => 'Cara isi', 'required' => true, 'options' => [
                    'tulis_penuh' => 'Tulis penuh', 'butir_ringkas' => 'Butir ringkas (AI)', 'kemudian' => 'Kemudian',
                ]],
                ['key' => 'message', 'type' => 'textarea', 'label' => 'Perutusan', 'ai' => true, 'max' => 2000],
            ],
            'visi_misi' => [
                ['key' => 'visi', 'type' => 'textarea', 'label' => 'Visi', 'max' => 500, 'template' => 'visi'],
                ['key' => 'misi', 'type' => 'textarea', 'label' => 'Misi', 'max' => 500, 'template' => 'misi'],
                ['key' => 'moto', 'type' => 'textarea', 'label' => 'Moto', 'max' => 200, 'template' => 'moto'],
            ],
            'ajk' => [
                ['key' => 'structure_note', 'type' => 'text', 'label' => 'Nota struktur'],
                ['key' => 'members', 'type' => 'repeater', 'label' => 'Ahli AJK', 'pdpa' => true, 'item' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'Nama', 'required' => true],
                    ['key' => 'position', 'type' => 'text', 'label' => 'Jawatan', 'required' => true],
                    ['key' => 'group', 'type' => 'select', 'label' => 'Kumpulan', 'options' => ['pengurusan' => 'Pengurusan', 'wanita' => 'Wanita', 'belia' => 'Belia']],
                    ['key' => 'photo', 'type' => 'upload', 'label' => 'Gambar'],
                ]],
                ['key' => 'full_list_later', 'type' => 'checkbox', 'label' => 'Senarai penuh akan dihantar kemudian'],
            ],
            'waktu_solat' => [
                ['key' => 'zone_confirm', 'type' => 'zone_display', 'label' => 'Zon solat'],
                ['key' => 'show_countdown', 'type' => 'checkbox', 'label' => 'Papar kiraan detik ke waktu seterusnya', 'default' => true],
                ['key' => 'show_hijri', 'type' => 'checkbox', 'label' => 'Papar tarikh Hijri', 'default' => true],
            ],
            'khutbah_jumaat' => [
                ['key' => 'mode', 'type' => 'radio', 'label' => 'Format', 'options' => ['arkib_pdf' => 'Arkib PDF', 'video' => 'Video', 'teks' => 'Teks']],
                ['key' => 'speaker_note', 'type' => 'text', 'label' => 'Penyampai tetap'],
            ],
            'live_streaming' => [
                ['key' => 'platform', 'type' => 'select', 'label' => 'Platform', 'required' => true, 'options' => ['YouTube' => 'YouTube', 'Facebook' => 'Facebook']],
                ['key' => 'channel_url', 'type' => 'url', 'label' => 'Pautan saluran', 'required' => true],
            ],
            'kuliah_mingguan' => [
                ['key' => 'sessions', 'type' => 'repeater', 'label' => 'Jadual kuliah', 'min' => 1, 'item' => [
                    ['key' => 'day', 'type' => 'select', 'label' => 'Hari', 'required' => true, 'options' => self::daysOptions()],
                    ['key' => 'time', 'type' => 'text', 'label' => 'Masa', 'required' => true, 'placeholder' => '8:30–9:30 malam'],
                    ['key' => 'topic', 'type' => 'text', 'label' => 'Tajuk', 'required' => true],
                    ['key' => 'speaker', 'type' => 'text', 'label' => 'Penceramah'],
                    ['key' => 'kitab', 'type' => 'text', 'label' => 'Kitab'],
                    ['key' => 'session', 'type' => 'select', 'label' => 'Sesi', 'options' => ['subuh' => 'Subuh', 'dhuha' => 'Dhuha', 'maghrib' => 'Maghrib', 'isyak' => 'Isyak', 'jumaat' => 'Jumaat']],
                ]],
            ],
            'kelas_quran' => [
                ['key' => 'classes', 'type' => 'repeater', 'label' => 'Kelas Al-Quran', 'item' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'Nama kelas', 'required' => true],
                    ['key' => 'level', 'type' => 'select', 'label' => 'Peringkat', 'required' => true, 'options' => self::quranLevelOptions()],
                    ['key' => 'days', 'type' => 'text', 'label' => 'Hari'],
                    ['key' => 'time', 'type' => 'text', 'label' => 'Masa'],
                    ['key' => 'location', 'type' => 'text', 'label' => 'Lokasi'],
                    ['key' => 'focus', 'type' => 'text', 'label' => 'Fokus', 'max' => 160],
                    ['key' => 'fee', 'type' => 'text', 'label' => 'Yuran'],
                ]],
            ],
            'nikah' => $serviceTemplate,
            'jenazah' => $serviceTemplate,
            'tahlil_doa' => $serviceTemplate,
            'khidmat_nasihat' => $serviceTemplate,
            'sewa_dewan' => array_merge($serviceTemplate, [
                ['key' => 'capacity', 'type' => 'number', 'label' => 'Kapasiti dewan'],
                ['key' => 'rates', 'type' => 'repeater', 'label' => 'Kadar sewa', 'item' => [
                    ['key' => 'pakej', 'type' => 'text', 'label' => 'Pakej'], ['key' => 'harga', 'type' => 'text', 'label' => 'Harga'],
                ]],
                ['key' => 'catering_panel', 'type' => 'checkbox', 'label' => 'Ada senarai panel katering'],
                ['key' => 'caterers', 'type' => 'repeater', 'label' => 'Panel katering', 'showIf' => ['catering_panel' => true], 'item' => [
                    ['key' => 'nama', 'type' => 'text', 'label' => 'Nama'], ['key' => 'telefon', 'type' => 'text', 'label' => 'Telefon'],
                ]],
            ]),
            'khairat' => [
                ['key' => 'monthly_fee', 'type' => 'text', 'label' => 'Yuran bulanan'],
                ['key' => 'terms', 'type' => 'textarea', 'label' => 'Terma & syarat', 'max' => 2000],
                ['key' => 'form_pdf', 'type' => 'upload', 'label' => 'Borang (PDF)'],
                ['key' => 'contact', 'type' => 'text', 'label' => 'Hubungi', 'required' => true],
            ],
            'fasiliti' => [
                ['key' => 'items', 'type' => 'facility_checklist', 'label' => 'Fasiliti tersedia'],
            ],
            'infaq' => [
                ['key' => 'categories', 'type' => 'repeater', 'label' => 'Kategori infaq', 'prefill' => 'infaq', 'item' => [
                    ['key' => 'icon', 'type' => 'select', 'label' => 'Ikon', 'options' => self::infaqIconOptions()],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Tajuk', 'required' => true],
                    ['key' => 'desc', 'type' => 'text', 'label' => 'Penerangan', 'max' => 160],
                ]],
                ['key' => 'bank_name', 'type' => 'text', 'label' => 'Nama bank', 'required' => true],
                ['key' => 'bank_account', 'type' => 'text', 'label' => 'Nombor akaun', 'required' => true],
                ['key' => 'account_holder', 'type' => 'text', 'label' => 'Nama pemegang akaun', 'required' => true],
                ['key' => 'qr_image', 'type' => 'upload', 'label' => 'Imej QR (DuitNow)'],
                ['key' => 'notice', 'type' => 'note', 'label' => 'Nombor akaun akan dipaparkan awam — sahkan betul.'],
            ],
            'berita' => [
                ['key' => 'seed_items', 'type' => 'repeater', 'label' => 'Berita permulaan (max 3)', 'max' => 3, 'item' => [
                    ['key' => 'tajuk', 'type' => 'text', 'label' => 'Tajuk'],
                    ['key' => 'tarikh', 'type' => 'text', 'label' => 'Tarikh'],
                    ['key' => 'ringkasan', 'type' => 'textarea', 'label' => 'Ringkasan', 'max' => 200],
                ]],
            ],
            'pengumuman' => [
                ['key' => 'seed_items', 'type' => 'repeater', 'label' => 'Pengumuman permulaan (max 3)', 'max' => 3, 'item' => [
                    ['key' => 'tajuk', 'type' => 'text', 'label' => 'Tajuk'],
                    ['key' => 'tarikh', 'type' => 'text', 'label' => 'Tarikh'],
                    ['key' => 'ringkasan', 'type' => 'textarea', 'label' => 'Ringkasan', 'max' => 200],
                ]],
            ],
            'galeri' => [
                ['key' => 'images', 'type' => 'upload_multi', 'label' => 'Gambar galeri (max 12)', 'max' => 12],
                ['key' => 'consent', 'type' => 'checkbox', 'label' => 'Saya mengesahkan kebenaran individu dalam gambar telah diperoleh, termasuk kebenaran penjaga bagi kanak-kanak.', 'consent' => true],
            ],
            'soalan_lazim' => [
                ['key' => 'faqs', 'type' => 'repeater', 'label' => 'Soalan Lazim', 'template' => 'faq', 'item' => [
                    ['key' => 'category', 'type' => 'select', 'label' => 'Kategori', 'options' => self::faqCategoryOptions()],
                    ['key' => 'q', 'type' => 'text', 'label' => 'Soalan', 'required' => true],
                    ['key' => 'a', 'type' => 'textarea', 'label' => 'Jawapan', 'required' => true, 'max' => 1000],
                ]],
            ],
            'info_pelawat' => [
                ['key' => 'visiting_hours', 'type' => 'repeater', 'label' => 'Waktu lawatan', 'item' => [
                    ['key' => 'hari', 'type' => 'text', 'label' => 'Hari'], ['key' => 'masa', 'type' => 'text', 'label' => 'Masa'],
                ]],
                ['key' => 'dress_code', 'type' => 'textarea', 'label' => 'Kod pakaian', 'max' => 500, 'template' => 'dress_code'],
                ['key' => 'getting_here', 'type' => 'textarea', 'label' => 'Cara ke sini (pengangkutan)', 'max' => 500],
                ['key' => 'tour_available', 'type' => 'checkbox', 'label' => 'Lawatan berpandu tersedia'],
                ['key' => 'tour_contact', 'type' => 'text', 'label' => 'Hubungi lawatan', 'showIf' => ['tour_available' => true]],
                ['key' => 'english_khutbah', 'type' => 'checkbox', 'label' => 'Khutbah dalam Bahasa Inggeris'],
            ],
            'hubungi' => [
                ['key' => 'office_hours', 'type' => 'repeater', 'label' => 'Waktu pejabat', 'item' => [
                    ['key' => 'hari', 'type' => 'text', 'label' => 'Hari'], ['key' => 'masa', 'type' => 'text', 'label' => 'Masa'],
                ]],
                ['key' => 'form_recipient_email', 'type' => 'email', 'label' => 'E-mel penerima borang', 'required' => true],
            ],
            'muat_turun' => [
                ['key' => 'documents', 'type' => 'repeater', 'label' => 'Dokumen (max 8)', 'max' => 8, 'item' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Tajuk', 'required' => true],
                    ['key' => 'file', 'type' => 'upload', 'label' => 'Fail PDF'],
                ]],
            ],
        ];
    }

    /** @return array<string, string> */
    public static function facilities(): array
    {
        return [
            'ruang_solat_utama' => 'Ruang solat utama',
            'ruang_solat_wanita' => 'Ruang solat wanita',
            'wuduk_lelaki' => 'Tempat wuduk lelaki',
            'wuduk_wanita' => 'Tempat wuduk wanita',
            'dewan' => 'Dewan',
            'bilik_kuliah' => 'Bilik kuliah',
            'perpustakaan' => 'Perpustakaan',
            'parkir' => 'Parkir',
            'oku' => 'Kemudahan OKU',
            'lif' => 'Lif',
            'wifi' => 'WiFi',
            'bilik_jenazah' => 'Bilik jenazah',
        ];
    }

    private static function quranLevelOptions(): array
    {
        $labels = ['tahsin' => 'Tahsin', 'hafazan' => 'Hafazan', 'tadabbur' => 'Tadabbur', 'dhuha' => 'Dhuha', 'tajwid' => 'Tajwid', 'ulum' => 'Ulum', 'qiraat' => 'Qiraat'];

        return array_intersect_key($labels, array_flip(self::QURAN_LEVELS));
    }

    private static function infaqIconOptions(): array
    {
        return array_combine(self::INFAQ_ICONS, self::INFAQ_ICONS);
    }

    private static function faqCategoryOptions(): array
    {
        $labels = ['umum' => 'Umum', 'pernikahan' => 'Pernikahan', 'jenazah' => 'Jenazah', 'dewan' => 'Dewan', 'kelas' => 'Kelas', 'infaq' => 'Infaq'];

        return array_intersect_key($labels, array_flip(self::FAQ_CATEGORIES));
    }

    private static function daysOptions(): array
    {
        return ['Isnin' => 'Isnin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Khamis' => 'Khamis', 'Jumaat' => 'Jumaat', 'Sabtu' => 'Sabtu', 'Ahad' => 'Ahad'];
    }

    /** Pra-isi kategori infaq (§6 L4). */
    public static function infaqPrefill(): array
    {
        return [
            ['icon' => 'HeartHandshake', 'title' => 'Infaq Am', 'desc' => 'Sumbangan am untuk operasi masjid.'],
            ['icon' => 'Building', 'title' => 'Wakaf', 'desc' => 'Wakaf tunai untuk pembangunan.'],
            ['icon' => 'HandHeart', 'title' => 'Pembinaan', 'desc' => 'Dana projek pembinaan & naik taraf.'],
            ['icon' => 'Users', 'title' => 'Anak Yatim', 'desc' => 'Bantuan untuk anak yatim & asnaf.'],
        ];
    }

    /** Pra-isi kategori derma NGO (§6 L4 / Fasa 11). */
    public static function dermaPrefill(): array
    {
        return [
            ['icon' => 'HeartHandshake', 'title' => 'Derma Am', 'desc' => 'Sumbangan am untuk operasi & aktiviti.'],
            ['icon' => 'BookOpen', 'title' => 'Dana Pendidikan', 'desc' => 'Bantuan pendidikan & biasiswa.'],
            ['icon' => 'Users', 'title' => 'Bantuan Asnaf', 'desc' => 'Bantuan untuk asnaf & keluarga memerlukan.'],
            ['icon' => 'Building', 'title' => 'Dana Operasi', 'desc' => 'Menampung kos operasi pertubuhan.'],
        ];
    }

    /**
     * Skema panel L4 NGO (§6 L4 / Fasa 11). Panel dikongsi (visi_misi/berita/pengumuman/
     * galeri/soalan_lazim/hubungi/muat_turun) guna semula skema masjid; jawatan & derma khusus NGO.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private static function ngoPanels(): array
    {
        $m = self::panels();

        return [
            'profil' => [
                ['key' => 'mode', 'type' => 'radio', 'label' => 'Cara isi', 'required' => true, 'options' => [
                    'tulis_penuh' => 'Tulis penuh sendiri', 'butir_ringkas' => 'Butir ringkas (AI karang)', 'kemudian' => 'Beri kemudian',
                ]],
                ['key' => 'full_text', 'type' => 'textarea', 'label' => 'Profil / sejarah', 'max' => 3000, 'showIf' => ['mode' => 'tulis_penuh']],
                ['key' => 'bullets', 'type' => 'repeater_text', 'label' => 'Butir ringkas', 'ai' => true, 'showIf' => ['mode' => 'butir_ringkas'], 'placeholder' => 'Cth: Ditubuhkan 2015, fokus kebajikan anak yatim'],
                ['key' => 'registration_no', 'type' => 'text', 'label' => 'No. Pendaftaran (ROS/SSM)'],
                ['key' => 'milestones', 'type' => 'repeater', 'label' => 'Peristiwa penting', 'item' => [
                    ['key' => 'tahun', 'type' => 'text', 'label' => 'Tahun'], ['key' => 'peristiwa', 'type' => 'text', 'label' => 'Peristiwa'],
                ]],
            ],
            'perutusan' => [
                ['key' => 'role', 'type' => 'select', 'label' => 'Jawatan', 'required' => true, 'options' => [
                    'Penaung' => 'Penaung', 'Pengerusi' => 'Pengerusi', 'Presiden' => 'Presiden',
                    'Timbalan Pengerusi' => 'Timbalan Pengerusi', 'Setiausaha' => 'Setiausaha',
                    'Bendahari' => 'Bendahari', 'Pengarah Eksekutif' => 'Pengarah Eksekutif',
                ]],
                ['key' => 'name', 'type' => 'text', 'label' => 'Nama', 'required' => true],
                ['key' => 'photo', 'type' => 'upload', 'label' => 'Gambar'],
                ['key' => 'mode', 'type' => 'radio', 'label' => 'Cara isi', 'required' => true, 'options' => [
                    'tulis_penuh' => 'Tulis penuh', 'butir_ringkas' => 'Butir ringkas (AI)', 'kemudian' => 'Kemudian',
                ]],
                ['key' => 'message', 'type' => 'textarea', 'label' => 'Perutusan', 'ai' => true, 'max' => 2000],
            ],
            'visi_misi' => $m['visi_misi'],
            'ajk' => [
                ['key' => 'structure_note', 'type' => 'text', 'label' => 'Nota struktur'],
                ['key' => 'members', 'type' => 'repeater', 'label' => 'Ahli Jawatankuasa', 'pdpa' => true, 'item' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'Nama', 'required' => true],
                    ['key' => 'position', 'type' => 'text', 'label' => 'Jawatan', 'required' => true],
                    ['key' => 'group', 'type' => 'select', 'label' => 'Kumpulan', 'options' => ['pengurusan' => 'Pengurusan', 'biro' => 'Biro', 'sukarelawan' => 'Sukarelawan']],
                    ['key' => 'photo', 'type' => 'upload', 'label' => 'Gambar'],
                ]],
                ['key' => 'full_list_later', 'type' => 'checkbox', 'label' => 'Senarai penuh akan dihantar kemudian'],
            ],
            'program_utama' => [
                ['key' => 'programs', 'type' => 'repeater', 'label' => 'Program & inisiatif', 'min' => 1, 'item' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'Nama program', 'required' => true],
                    ['key' => 'desc', 'type' => 'textarea', 'label' => 'Penerangan', 'max' => 300],
                    ['key' => 'audience', 'type' => 'text', 'label' => 'Sasaran'],
                    ['key' => 'schedule', 'type' => 'text', 'label' => 'Jadual / kekerapan'],
                ]],
            ],
            'program_akan_datang' => [
                ['key' => 'seed_items', 'type' => 'repeater', 'label' => 'Acara akan datang (max 3)', 'max' => 3, 'item' => [
                    ['key' => 'tajuk', 'type' => 'text', 'label' => 'Tajuk'],
                    ['key' => 'tarikh', 'type' => 'text', 'label' => 'Tarikh'],
                    ['key' => 'lokasi', 'type' => 'text', 'label' => 'Lokasi'],
                ]],
            ],
            'berita' => $m['berita'],
            'pengumuman' => $m['pengumuman'],
            'galeri' => $m['galeri'],
            'sukarelawan' => [
                ['key' => 'intro', 'type' => 'textarea', 'label' => 'Pengenalan sukarelawan', 'ai' => true, 'max' => 1000],
                ['key' => 'roles', 'type' => 'repeater', 'label' => 'Bidang sukarelawan', 'item' => [
                    ['key' => 'bidang', 'type' => 'text', 'label' => 'Bidang'], ['key' => 'komitmen', 'type' => 'text', 'label' => 'Komitmen masa'],
                ]],
                ['key' => 'form_url', 'type' => 'url', 'label' => 'Pautan borang sukarelawan'],
                ['key' => 'contact', 'type' => 'text', 'label' => 'Hubungi', 'required' => true],
            ],
            'keahlian' => [
                ['key' => 'criteria', 'type' => 'textarea', 'label' => 'Syarat keahlian', 'max' => 1000],
                ['key' => 'fee', 'type' => 'text', 'label' => 'Yuran keahlian'],
                ['key' => 'benefits', 'type' => 'repeater_text', 'label' => 'Manfaat ahli', 'placeholder' => 'Cth: Diskaun program'],
                ['key' => 'form_pdf', 'type' => 'upload', 'label' => 'Borang keahlian (PDF)'],
                ['key' => 'contact', 'type' => 'text', 'label' => 'Hubungi', 'required' => true],
            ],
            'derma' => [
                ['key' => 'categories', 'type' => 'repeater', 'label' => 'Kategori derma', 'prefill' => 'derma', 'item' => [
                    ['key' => 'icon', 'type' => 'select', 'label' => 'Ikon', 'options' => self::infaqIconOptions()],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Tajuk', 'required' => true],
                    ['key' => 'desc', 'type' => 'text', 'label' => 'Penerangan', 'max' => 160],
                ]],
                ['key' => 'bank_name', 'type' => 'text', 'label' => 'Nama bank', 'required' => true],
                ['key' => 'bank_account', 'type' => 'text', 'label' => 'Nombor akaun', 'required' => true],
                ['key' => 'account_holder', 'type' => 'text', 'label' => 'Nama pemegang akaun', 'required' => true],
                ['key' => 'qr_image', 'type' => 'upload', 'label' => 'Imej QR (DuitNow)'],
                ['key' => 'notice', 'type' => 'note', 'label' => 'Nombor akaun akan dipaparkan awam — sahkan betul.'],
            ],
            'soalan_lazim' => $m['soalan_lazim'],
            'hubungi' => $m['hubungi'],
            'muat_turun' => $m['muat_turun'],
        ];
    }
}
