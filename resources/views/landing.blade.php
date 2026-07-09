@extends('layouts.public')

@section('title', 'REKA — Laman Web Rasmi Masjid Anda')

@php
    // Pameran 5 pakej reka — selaras DesignPackageSeeder (§7.2).
    $packages = [
        ['name' => 'Warisan Hijau', 'colors' => ['#1B5E3F', '#0F3D27', '#C9A961'], 'font' => 'Cormorant Garamond', 'for' => 'Serba guna — terbukti produksi'],
        ['name' => 'Biru Nilam', 'colors' => ['#1D4E89', '#10315C', '#B08D3E'], 'font' => 'Playfair Display', 'for' => 'Masjid bandar moden'],
        ['name' => 'Emas Kubah', 'colors' => ['#8C6D2F', '#5C4620', '#1B5E3F'], 'font' => 'Lora', 'for' => 'Masjid bersejarah / klasik'],
        ['name' => 'Teal Kontemporari', 'colors' => ['#0F6E6E', '#084C4C', '#E0A94F'], 'font' => 'IBM Plex Serif', 'for' => 'Komuniti muda / mesra keluarga'],
        ['name' => 'Marun Agung', 'colors' => ['#6E1F2E', '#4A121D', '#C9A961'], 'font' => 'Cormorant Garamond', 'for' => 'Masjid besar / kerajaan'],
    ];
    $prayers = [['Subuh', '5:52'], ['Zohor', '13:15'], ['Asar', '16:38'], ['Maghrib', '19:29'], ['Isyak', '20:41']];
@endphp

@section('content')
    {{-- ══ 1. HERO ══ --}}
    <section class="relative overflow-hidden bg-brand-950 text-cream">
        {{-- Lapisan latar --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-900 via-brand-950 to-black"></div>
            <div class="absolute -top-40 -left-32 h-[32rem] w-[32rem] rounded-full bg-brand-500/20 blur-[120px]"></div>
            <div class="absolute -right-20 bottom-0 h-[28rem] w-[28rem] rounded-full bg-gold-500/15 blur-[120px]"></div>
            <div class="bg-pattern-islamic absolute inset-0 opacity-[0.5]"></div>
            <div class="bg-noise absolute inset-0 opacity-[0.04]"></div>
        </div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:py-28">
            {{-- Kiri: mesej --}}
            <div class="reveal">
                <span class="eyebrow-dark">
                    {!! \App\Support\Lucide::svg('Sparkles', 1.75, 'h-3.5 w-3.5') !!}
                    Untuk masjid, surau &amp; pertubuhan Islam di Malaysia
                </span>
                <h1 class="mt-6 font-display text-5xl leading-[1.02] font-bold tracking-tight sm:text-6xl lg:text-[4.25rem]">
                    Laman web masjid anda —<br>
                    <span class="text-gradient-gold">direka khusus,</span><br>
                    bukan template.
                </h1>
                <p class="mt-6 max-w-lg text-lg/relaxed text-cream/70">
                    Domain sendiri, reka bentuk tersendiri, kandungan lengkap, sistem infaq &amp; kariah —
                    bukan sekadar paparan waktu solat seragam.
                </p>
                <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                    <x-ui.button :href="route('minat.create')" variant="gold" size="lg">
                        Daftar Minat Sekarang
                        {!! \App\Support\Lucide::svg('HeartHandshake', 2, 'h-5 w-5') !!}
                    </x-ui.button>
                    <x-ui.button href="#cara" variant="on-dark" size="lg">Lihat Cara Kerja</x-ui.button>
                </div>
                <p class="mt-6 flex items-center gap-2 text-sm text-cream/50">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                    Percuma untuk daftar · tiada komitmen
                </p>
            </div>

            {{-- Kanan: mockup browser terapung (mini laman masjid, HTML/CSS tulen) --}}
            <div class="reveal reveal-d2 relative lg:pl-6">
                <div class="animate-float-slow rounded-2xl border border-white/10 bg-white shadow-[0_40px_90px_-30px_rgba(0,0,0,0.7)] ring-1 ring-black/5">
                    {{-- bar browser --}}
                    <div class="flex items-center gap-2 rounded-t-2xl border-b border-black/5 bg-[#F3EFE8] px-4 py-2.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#E4654A]"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-[#E3B341]"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-[#54A362]"></span>
                        <span class="ml-3 flex-1 truncate rounded-md bg-white px-3 py-1 text-center text-[11px] text-ink/40">masjidanda.my</span>
                    </div>
                    {{-- kandungan mini laman (gaya Warisan Hijau) --}}
                    <div class="overflow-hidden rounded-b-2xl bg-[#FAF7F2]">
                        <div class="flex items-center justify-between bg-[#0F3D27] px-4 py-2.5">
                            <div class="flex items-center gap-1.5">
                                <span class="grid h-4 w-4 place-items-center rounded bg-[#C9A961] text-[9px] font-bold text-[#0F3D27]">م</span>
                                <span class="text-[11px] font-semibold text-white">Masjid Al-Hidayah</span>
                            </div>
                            <div class="hidden gap-1 sm:flex">
                                <span class="h-1 w-4 rounded-full bg-white/40"></span>
                                <span class="h-1 w-4 rounded-full bg-white/25"></span>
                                <span class="h-1 w-4 rounded-full bg-white/25"></span>
                            </div>
                        </div>
                        <div class="px-5 py-6 text-center">
                            <p class="text-[10px] font-semibold tracking-widest text-[#C9A961] uppercase">Selamat Datang</p>
                            <p class="mt-1 font-display text-2xl font-bold text-[#0F3D27]">Rumah Ibadah Komuniti</p>
                            <div class="mt-3 inline-flex gap-1.5">
                                <span class="rounded-full bg-[#1B5E3F] px-3 py-1 text-[9px] font-semibold text-white">Infaq</span>
                                <span class="rounded-full border border-[#1B5E3F]/25 px-3 py-1 text-[9px] font-semibold text-[#1B5E3F]">Kelas</span>
                            </div>
                        </div>
                        {{-- jalur waktu solat --}}
                        <div class="mx-4 grid grid-cols-5 gap-1 rounded-lg bg-white p-2 shadow-sm ring-1 ring-[#EFE8DC]">
                            @foreach ($prayers as [$name, $time])
                                <div class="text-center">
                                    <p class="text-[7px] font-medium text-ink/45">{{ $name }}</p>
                                    <p class="text-[9px] font-bold text-[#1B5E3F]">{{ $time }}</p>
                                </div>
                            @endforeach
                        </div>
                        {{-- kad khidmat --}}
                        <div class="grid grid-cols-3 gap-2 p-4">
                            @foreach (['HandHeart' => 'Infaq', 'GraduationCap' => 'Kelas', 'Users' => 'Kariah'] as $icon => $label)
                                <div class="rounded-lg bg-white p-2.5 text-center shadow-sm ring-1 ring-[#EFE8DC]">
                                    <span class="mx-auto grid h-6 w-6 place-items-center rounded-full bg-[#1B5E3F]/8 text-[#1B5E3F]">
                                        {!! \App\Support\Lucide::svg($icon, 1.75, 'h-3.5 w-3.5') !!}
                                    </span>
                                    <p class="mt-1 text-[8px] font-medium text-ink/60">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                {{-- lencana terapung --}}
                <div class="absolute -bottom-4 -left-4 hidden rounded-xl bg-white px-3.5 py-2.5 shadow-lift ring-1 ring-sand sm:block">
                    <p class="flex items-center gap-1.5 text-xs font-semibold text-brand-700">
                        {!! \App\Support\Lucide::svg('Sparkles', 2, 'h-4 w-4 text-gold-500') !!}
                        Draf dijana AI
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ 2. JALUR KEPERCAYAAN ══ --}}
    <section class="border-b border-sand bg-white">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-px overflow-hidden px-4 sm:px-6 lg:grid-cols-4">
            @foreach ([
                ['BookOpen', '10 langkah', 'wizard berpandu'],
                ['Sparkles', '5 pakej', 'reka bentuk eksklusif'],
                ['Clock', 'Beberapa minit', 'draf AI dijana'],
                ['Landmark', '100% milik', 'masjid anda sendiri'],
            ] as [$icon, $big, $small])
                <div class="flex items-center gap-3 py-6 lg:justify-center">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">
                        {!! \App\Support\Lucide::svg($icon, 1.75, 'h-5 w-5') !!}
                    </span>
                    <div>
                        <p class="font-display text-lg font-bold text-brand-800">{{ $big }}</p>
                        <p class="text-xs text-ink/55">{{ $small }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══ 3. CARA KERJA (3 langkah) ══ --}}
    <section id="cara" class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
        <x-ui.section-heading eyebrow="Cara Kerja" title="Tiga langkah mudah">
            Tanpa perlu tahu teknikal — kami pandu setiap langkah sehingga laman anda siap.
        </x-ui.section-heading>

        <div class="mt-14 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['1', 'Mail', 'Daftar minat', 'Isi borang ringkas. Kami hubungi anda dalam 2 hari bekerja.'],
                ['2', 'BookOpen', 'Isi maklumat berpandu', 'Wizard 10 langkah dengan contoh & pilihan visual — mudah difahami.'],
                ['3', 'Sparkles', 'Terima laman siap', 'Kami jana draf, anda semak & luluskan, kemudian kami bina laman sebenar.'],
            ] as $i => [$num, $icon, $title, $desc])
                <div class="reveal reveal-d{{ $i + 1 }} group relative">
                    <x-ui.card class="h-full transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-lift">
                        <div class="flex items-center gap-3">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-cream shadow-soft">
                                {!! \App\Support\Lucide::svg($icon, 1.75, 'h-6 w-6') !!}
                            </span>
                            <span class="font-display text-4xl font-bold text-sand">{{ $num }}</span>
                        </div>
                        <h3 class="mt-5 text-lg font-semibold text-brand-800">{{ $title }}</h3>
                        <p class="mt-2 text-sm/relaxed text-ink/65">{{ $desc }}</p>
                    </x-ui.card>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══ 4. PAMERAN PAKEJ REKA ══ --}}
    <section id="pakej" class="relative overflow-hidden bg-brand-950 py-20 text-cream sm:py-28">
        <div class="bg-pattern-islamic absolute inset-0 opacity-30" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
            <x-ui.section-heading eyebrow="Pakej Reka" title="Lima gaya, satu identiti anda" :dark="true">
                Pilih pakej yang paling sepadan dengan peribadi masjid anda. Setiap satu ditala tangan —
                warna, tipografi, dan susun atur.
            </x-ui.section-heading>

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $i => $pkg)
                    <div class="reveal reveal-d{{ min($i + 1, 5) }} group rounded-2xl border border-white/10 bg-white/[0.04] p-5 backdrop-blur-sm transition-all duration-300 hover:border-gold-400/30 hover:bg-white/[0.07]">
                        {{-- swatch warna --}}
                        <div class="flex h-24 overflow-hidden rounded-xl shadow-inner ring-1 ring-white/10">
                            @foreach ($pkg['colors'] as $c)
                                <div class="flex-1 transition-all duration-300 group-hover:flex-[1.3]" style="background: {{ $c }}"></div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-display text-xl font-bold text-cream">{{ $pkg['name'] }}</h3>
                                <p class="mt-0.5 text-xs text-cream/50">Aksara: {{ $pkg['font'] }}</p>
                            </div>
                            <span class="flex gap-1">
                                @foreach ($pkg['colors'] as $c)
                                    <span class="h-3 w-3 rounded-full ring-1 ring-white/20" style="background: {{ $c }}"></span>
                                @endforeach
                            </span>
                        </div>
                        <p class="mt-3 flex items-center gap-1.5 text-xs text-cream/60">
                            {!! \App\Support\Lucide::svg('Landmark', 1.75, 'h-3.5 w-3.5 text-gold-300') !!}
                            Sesuai untuk: {{ $pkg['for'] }}
                        </p>
                    </div>
                @endforeach

                {{-- kad ajakan --}}
                <div class="reveal reveal-d5 flex flex-col items-start justify-center rounded-2xl border border-gold-400/20 bg-gradient-to-br from-gold-500/10 to-transparent p-6">
                    <p class="font-display text-2xl font-bold text-cream">Belum pasti?</p>
                    <p class="mt-2 text-sm text-cream/65">Kami cadangkan pakej terbaik untuk masjid anda semasa proses.</p>
                    <x-ui.button :href="route('minat.create')" variant="gold" size="sm" class="mt-5">Mula sekarang</x-ui.button>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ 5. PERBANDINGAN (WAJIB §1.2) ══ --}}
    <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
        <x-ui.section-heading eyebrow="Kenapa REKA" title="Kenapa bayar bila ada platform percuma?">
            Anda mendapat <strong class="text-ink/80">laman custom penuh identiti masjid sendiri</strong> —
            berbanding template seragam yang menjadikan semua laman kelihatan sama.
        </x-ui.section-heading>

        <div class="mt-14 grid gap-6 md:grid-cols-2">
            {{-- platform percuma --}}
            <div class="reveal rounded-2xl border border-sand bg-white/60 p-7">
                <h3 class="font-semibold text-ink/50">Platform percuma seragam</h3>
                <ul class="mt-5 space-y-3.5 text-sm text-ink/60">
                    @foreach ([
                        'Subdomain kongsi, bukan domain sendiri',
                        'Template sama untuk semua masjid',
                        'Fokus paparan waktu solat sahaja',
                        'Tiada kandungan & identiti penuh',
                    ] as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-ink/5 text-ink/40">
                                {!! \App\Support\Lucide::svg('X', 2.25, 'h-3 w-3') !!}
                            </span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
            {{-- laman REKA --}}
            <div class="reveal reveal-d1 relative rounded-2xl bg-white p-7 shadow-lift ring-2 ring-gold-400/40">
                <span class="badge absolute -top-3 right-6 bg-gold-400 text-brand-900 shadow-soft">Pilihan Bijak</span>
                <h3 class="font-display text-xl font-bold text-brand-800">Laman REKA</h3>
                <ul class="mt-5 space-y-3.5 text-sm text-ink/80">
                    @foreach ([
                        'Domain & jenama sendiri',
                        'Reka bentuk tersendiri (warna, font, susun atur)',
                        'Kandungan penuh — sejarah, kelas, khidmat kariah, galeri',
                        'Sistem infaq & pautan kariah',
                    ] as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand-600 text-white">
                                {!! \App\Support\Lucide::svg('Check', 2.5, 'h-3 w-3') !!}
                            </span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <p class="mt-10 text-center text-sm text-ink/55">
            Contoh kualiti kerja kami:
            <a href="https://mamkl.my" target="_blank" rel="noopener nofollow" class="font-medium text-brand-600 underline decoration-gold-400/50 underline-offset-2 hover:text-brand-700">mamkl.my</a>
            ·
            <a href="https://www.masjidwilayah.gov.my" target="_blank" rel="noopener nofollow" class="font-medium text-brand-600 underline decoration-gold-400/50 underline-offset-2 hover:text-brand-700">masjidwilayah.gov.my</a>
        </p>
    </section>

    {{-- ══ 6. PROSES TERPERINCI ══ --}}
    <section class="border-y border-sand bg-cream">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
            <x-ui.section-heading eyebrow="Perjalanan" title="Dari jemputan ke laman siap">
                Enam fasa jelas — anda sentiasa tahu di mana kedudukan projek anda.
            </x-ui.section-heading>

            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Mail', 'Jemputan', 'Kami hantar pautan peribadi & selamat kepada wakil masjid (PIC).'],
                    ['BookOpen', 'Wizard 10 langkah', 'Isi maklumat masjid dengan panduan, contoh, dan pilihan visual.'],
                    ['Sparkles', 'Draf AI', 'Sistem menjana draf sampel laman dalam beberapa minit.'],
                    ['Users', 'Semak bersama', 'PIC & AJK menyemak draf, minta tweak reka bentuk atau kandungan.'],
                    ['HeartHandshake', 'Kelulusan', 'Setelah berpuas hati, PIC meluluskan — snapshot dibekukan.'],
                    ['Landmark', 'Serahan', 'Kami bina laman sebenar & serahkan pakej lengkap kepada masjid.'],
                ] as $i => [$icon, $title, $desc])
                    <div class="reveal reveal-d{{ min($i + 1, 5) }} flex gap-4 rounded-2xl bg-white p-5 shadow-soft ring-1 ring-sand">
                        <div class="flex flex-col items-center">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-600 text-cream shadow-soft">
                                {!! \App\Support\Lucide::svg($icon, 1.75, 'h-5 w-5') !!}
                            </span>
                            <span class="mt-2 font-display text-xs font-bold text-gold-500">0{{ $i + 1 }}</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-brand-800">{{ $title }}</h3>
                            <p class="mt-1 text-sm/relaxed text-ink/60">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ 7. SOALAN LAZIM (FAQ, CSS sahaja) ══ --}}
    <section id="soalan" class="mx-auto max-w-3xl px-4 py-20 sm:px-6 sm:py-28">
        <x-ui.section-heading eyebrow="Soalan Lazim" title="Perkara yang sering ditanya" />

        <div class="mt-12 space-y-3">
            @foreach ([
                ['Berapa kos dan bagaimana bayaran?', 'Pelaburan RM3,000 sekali bina (tidak termasuk domain & hosting). Penyelenggaraan tahunan pilihan dari RM1,000/tahun. Bayaran diuruskan melalui invois selepas perbincangan. Pendaftaran minat percuma & tiada komitmen.'],
                ['Berapa lama proses sehingga laman siap?', 'Selepas anda melengkapkan wizard dan meluluskan draf, kami membina laman sebenar. Draf sampel pula dijana dalam beberapa minit sahaja.'],
                ['Adakah saya perlu tahu teknikal?', 'Tidak. Wizard 10 langkah memandu anda dengan contoh dan pilihan visual — anda hanya perlu maklumat masjid.'],
                ['Bolehkah guna domain sendiri?', 'Ya. Laman REKA menggunakan domain & jenama masjid anda sendiri, bukan subdomain kongsi.'],
                ['Siapa yang memiliki laman selepas siap?', 'Masjid memiliki sepenuhnya laman dan kandungannya. Kami serahkan pakej lengkap kepada pihak masjid.'],
                ['Bagaimana AI digunakan?', 'AI membantu menjana draf kandungan cadangan sahaja. Teks Al-Quran & waktu solat tidak dijana AI — ia dari sumber rasmi. Anda menyemak & meluluskan segalanya.'],
            ] as $i => [$q, $a])
                <details class="reveal group rounded-2xl bg-white px-5 shadow-soft ring-1 ring-sand [&_svg]:open:rotate-45">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 font-semibold text-brand-800 marker:hidden">
                        {{ $q }}
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600 transition-transform duration-200">
                            {!! \App\Support\Lucide::svg('Plus', 2, 'h-4 w-4') !!}
                        </span>
                    </summary>
                    <p class="pb-5 text-sm/relaxed text-ink/65">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ══ 8. CTA AKHIR ══ --}}
    <section class="relative overflow-hidden bg-brand-800">
        <div class="bg-pattern-islamic-lg absolute inset-0 opacity-50" aria-hidden="true"></div>
        <div class="absolute -top-24 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-gold-500/20 blur-[100px]" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6 sm:py-24">
            <h2 class="font-display text-4xl font-bold text-cream sm:text-5xl">Sedia untuk laman masjid anda sendiri?</h2>
            <p class="mx-auto mt-4 max-w-xl text-cream/70">Daftar minat percuma — tiada komitmen. Kami hubungi anda dalam 2 hari bekerja.</p>
            <div class="mt-10">
                <x-ui.button :href="route('minat.create')" variant="gold" size="lg">
                    Daftar Minat Sekarang
                    {!! \App\Support\Lucide::svg('HeartHandshake', 2, 'h-5 w-5') !!}
                </x-ui.button>
            </div>
        </div>
    </section>
@endsection
