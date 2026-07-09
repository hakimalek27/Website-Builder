@extends('layouts.public')

@section('title', 'Notis Privasi — REKA')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-20">
        <div class="text-center">
            <span class="eyebrow">Perundangan</span>
            <h1 class="mt-4 font-display text-4xl font-bold text-brand-800">Notis Privasi</h1>
            <p class="mt-1 text-sm text-ink/50">Privacy Notice</p>
            <p class="mt-3 text-xs text-gold-600">Dwibahasa mengikut keperluan notis s.7 Akta 709. (Draf — untuk semakan perundangan.)</p>
        </div>

        <div class="mx-auto mt-6 h-px w-24 bg-gradient-to-r from-transparent via-gold-400 to-transparent"></div>

        <div class="mt-10 space-y-4">
            @php
                $sections = [
                    ['Pengawal Data / Data Controller', config('reka.business_name') . ' bertindak sebagai pengawal data bagi maklumat yang anda berikan melalui platform REKA.', config('reka.business_name') . ' acts as the data controller for information you provide through the REKA platform.'],
                    ['Data yang dikutip / Data collected', 'Nama, telefon, emel PIC; nama & maklumat masjid; nama/jawatan/gambar AJK (jika diberi); gambar galeri; nombor akaun bank masjid; alamat IP semasa kelulusan.', 'PIC name, phone, email; mosque name & details; committee names/positions/photos (if provided); gallery images; mosque bank account; IP address at approval.'],
                    ['Tujuan / Purpose', 'Penyediaan cadangan dan pembinaan laman web masjid.', 'Preparing proposals and building the mosque website.'],
                    ['Pendedahan kepada pemproses / Disclosure to processors', 'Kandungan mungkin diproses oleh penyedia perkhidmatan AI & pengehosan yang mungkin berada di luar Malaysia. PII diminimumkan sebelum dihantar kepada AI.', 'Content may be processed by AI service providers & hosting which may be located outside Malaysia. PII is minimised before being sent to AI.'],
                    ['Tempoh simpanan / Retention', 'Lead ditolak 6 bulan; projek dibatalkan/luput 12 bulan; log 24 bulan; projek siap disimpan sebagai rekod kontrak.', 'Rejected leads 6 months; cancelled/expired projects 12 months; logs 24 months; completed projects retained as contract records.'],
                ];
            @endphp

            @foreach ($sections as $i => [$title, $bm, $en])
                <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-sand">
                    <h2 class="flex items-center gap-3 text-lg font-semibold text-brand-800">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 font-display text-sm font-bold text-brand-600">{{ $i + 1 }}</span>
                        {{ $title }}
                    </h2>
                    <p class="mt-3 text-sm/relaxed text-ink/70"><strong class="font-semibold text-ink/80">BM:</strong> {{ $bm }}</p>
                    <p class="mt-2 text-sm/relaxed text-ink/60"><strong class="font-semibold text-ink/70">EN:</strong> {{ $en }}</p>
                </div>
            @endforeach

            <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-sand">
                <h2 class="flex items-center gap-3 text-lg font-semibold text-brand-800">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 font-display text-sm font-bold text-brand-600">6</span>
                    Hak anda / Your rights
                </h2>
                <p class="mt-3 text-sm/relaxed text-ink/70"><strong class="font-semibold text-ink/80">BM:</strong> Akses, pembetulan, tarik balik persetujuan, dan mudah alih data. Untuk memohon, hubungi <a href="mailto:privasi@reka.example.my" class="text-brand-600 underline">privasi@reka.example.my</a>.</p>
                <p class="mt-2 text-sm/relaxed text-ink/60"><strong class="font-semibold text-ink/70">EN:</strong> Access, correction, withdrawal of consent, and data portability. To request, contact <a href="mailto:privasi@reka.example.my" class="text-brand-600 underline">privasi@reka.example.my</a>.</p>
            </div>
        </div>
    </section>
@endsection
