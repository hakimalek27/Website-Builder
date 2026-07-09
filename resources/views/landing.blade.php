@extends('layouts.public')

@section('title', 'REKA — Laman Web Rasmi Masjid Anda')

@section('content')
    {{-- Hero (§5.1) --}}
    <section class="relative overflow-hidden">
        <div class="mx-auto max-w-6xl px-4 py-20 sm:py-28 text-center">
            <span class="inline-block rounded-full bg-[#1B5E3F]/10 px-4 py-1 text-sm font-medium text-[#0F3D27]">
                Untuk masjid & surau di Malaysia
            </span>
            <h1 class="mt-6 text-4xl sm:text-5xl font-bold tracking-tight text-[#0F3D27]">
                Laman web rasmi masjid anda —<br class="hidden sm:block">
                <span class="text-[#1B5E3F]">direka khusus, bukan template.</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-[#1A1A1A]/75">
                Domain sendiri, reka bentuk tersendiri, kandungan lengkap, sistem infaq & kariah —
                bukan sekadar paparan waktu solat seragam.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('minat.create') }}"
                   class="rounded-xl bg-[#1B5E3F] px-8 py-3.5 text-base font-semibold text-white shadow-sm hover:bg-[#0F3D27] transition">
                    Daftar Minat Sekarang
                </a>
                <a href="#cara"
                   class="rounded-xl border border-[#1B5E3F]/30 px-8 py-3.5 text-base font-semibold text-[#0F3D27] hover:bg-[#EFE8DC] transition">
                    Bagaimana ia berfungsi
                </a>
            </div>
        </div>
    </section>

    {{-- 3 langkah cara berfungsi (§5.1) --}}
    <section id="cara" class="bg-white/50 border-y border-[#EFE8DC]">
        <div class="mx-auto max-w-6xl px-4 py-16">
            <h2 class="text-center text-3xl font-bold text-[#0F3D27]">Tiga langkah mudah</h2>
            <div class="mt-12 grid gap-8 sm:grid-cols-3">
                @foreach ([
                    ['1', 'Daftar minat', 'Isi borang ringkas. Kami hubungi anda dalam 2 hari bekerja.'],
                    ['2', 'Isi maklumat berpandu', 'Wizard 10 langkah dengan contoh & pilihan visual — tiada perlu tahu teknikal.'],
                    ['3', 'Terima laman siap', 'Kami jana draf, anda semak & luluskan, kemudian kami bina laman sebenar.'],
                ] as [$num, $title, $desc])
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#EFE8DC]">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#1B5E3F] text-lg font-bold text-white">{{ $num }}</div>
                        <h3 class="mt-4 text-lg font-semibold text-[#0F3D27]">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-[#1A1A1A]/70">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Perbandingan vs platform percuma (§1.2) --}}
    <section class="mx-auto max-w-6xl px-4 py-16">
        <h2 class="text-center text-3xl font-bold text-[#0F3D27]">Kenapa bayar bila ada platform percuma?</h2>
        <p class="mx-auto mt-4 max-w-3xl text-center text-[#1A1A1A]/75">
            Anda mendapat <strong>laman custom penuh identiti masjid sendiri</strong> (domain, reka bentuk, kandungan
            lengkap, sistem infaq/kariah) berbanding template seragam yang menjadikan semua laman kelihatan sama.
        </p>
        <div class="mt-10 grid gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-[#EFE8DC] bg-white/60 p-6">
                <h3 class="font-semibold text-[#1A1A1A]/60">Platform percuma seragam</h3>
                <ul class="mt-4 space-y-2 text-sm text-[#1A1A1A]/70">
                    <li>• Subdomain kongsi, bukan domain sendiri</li>
                    <li>• Template sama untuk semua masjid</li>
                    <li>• Fokus paparan waktu solat sahaja</li>
                    <li>• Tiada kandungan & identiti penuh</li>
                </ul>
            </div>
            <div class="rounded-2xl border-2 border-[#1B5E3F] bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-[#0F3D27]">Laman REKA</h3>
                <ul class="mt-4 space-y-2 text-sm text-[#1A1A1A]/80">
                    <li>✓ Domain & jenama sendiri</li>
                    <li>✓ Reka bentuk tersendiri (pilihan warna, font, susun atur)</li>
                    <li>✓ Kandungan penuh — sejarah, kelas, khidmat kariah, galeri</li>
                    <li>✓ Sistem infaq & pautan kariah</li>
                </ul>
            </div>
        </div>
        <p class="mt-8 text-center text-sm text-[#1A1A1A]/60">
            Contoh kualiti kerja kami:
            <a href="https://mamkl.my" target="_blank" rel="noopener nofollow" class="text-[#1B5E3F] underline">mamkl.my</a> ·
            <a href="https://www.masjidwilayah.gov.my" target="_blank" rel="noopener nofollow" class="text-[#1B5E3F] underline">masjidwilayah.gov.my</a>
        </p>
    </section>

    {{-- CTA akhir --}}
    <section class="bg-[#0F3D27]">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center">
            <h2 class="text-3xl font-bold text-white">Sedia untuk laman masjid anda sendiri?</h2>
            <p class="mt-3 text-[#FAF7F2]/80">Daftar minat percuma — tiada komitmen.</p>
            <a href="{{ route('minat.create') }}"
               class="mt-8 inline-block rounded-xl bg-[#C9A961] px-8 py-3.5 text-base font-semibold text-[#0F3D27] hover:brightness-105 transition">
                Daftar Minat Sekarang
            </a>
        </div>
    </section>
@endsection
