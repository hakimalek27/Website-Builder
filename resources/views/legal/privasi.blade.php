@extends('layouts.public')

@section('title', 'Notis Privasi — REKA')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-16">
        <h1 class="text-3xl font-bold text-[#0F3D27]">Notis Privasi / Privacy Notice</h1>
        <p class="mt-2 text-xs text-[#8C6D2F]">Dwibahasa mengikut keperluan notis s.7 Akta 709. (Draf — untuk semakan perundangan.)</p>

        <div class="prose prose-sm mt-6 max-w-none text-[#1A1A1A]/80 space-y-3">
            <h2 class="text-lg font-semibold text-[#0F3D27]">1. Pengawal Data / Data Controller</h2>
            <p><strong>BM:</strong> {{ config('reka.business_name') }} bertindak sebagai pengawal data bagi maklumat yang anda berikan melalui platform REKA.</p>
            <p><strong>EN:</strong> {{ config('reka.business_name') }} acts as the data controller for information you provide through the REKA platform.</p>

            <h2 class="text-lg font-semibold text-[#0F3D27]">2. Data yang dikutip / Data collected</h2>
            <p><strong>BM:</strong> Nama, telefon, emel PIC; nama & maklumat masjid; nama/jawatan/gambar AJK (jika diberi); gambar galeri; nombor akaun bank masjid; alamat IP semasa kelulusan.</p>
            <p><strong>EN:</strong> PIC name, phone, email; mosque name & details; committee names/positions/photos (if provided); gallery images; mosque bank account; IP address at approval.</p>

            <h2 class="text-lg font-semibold text-[#0F3D27]">3. Tujuan / Purpose</h2>
            <p><strong>BM:</strong> Penyediaan cadangan dan pembinaan laman web masjid.</p>
            <p><strong>EN:</strong> Preparing proposals and building the mosque website.</p>

            <h2 class="text-lg font-semibold text-[#0F3D27]">4. Pendedahan kepada pemproses / Disclosure to processors</h2>
            <p><strong>BM:</strong> Kandungan mungkin diproses oleh penyedia perkhidmatan AI & pengehosan yang mungkin berada di luar Malaysia. PII diminimumkan sebelum dihantar kepada AI.</p>
            <p><strong>EN:</strong> Content may be processed by AI service providers & hosting which may be located outside Malaysia. PII is minimised before being sent to AI.</p>

            <h2 class="text-lg font-semibold text-[#0F3D27]">5. Tempoh simpanan / Retention</h2>
            <p><strong>BM:</strong> Lead ditolak 6 bulan; projek dibatalkan/luput 12 bulan; log 24 bulan; projek siap disimpan sebagai rekod kontrak.</p>
            <p><strong>EN:</strong> Rejected leads 6 months; cancelled/expired projects 12 months; logs 24 months; completed projects retained as contract records.</p>

            <h2 class="text-lg font-semibold text-[#0F3D27]">6. Hak anda / Your rights</h2>
            <p><strong>BM:</strong> Akses, pembetulan, tarik balik persetujuan, dan mudah alih data. Untuk memohon, hubungi <a href="mailto:privasi@reka.example.my" class="text-[#1B5E3F] underline">privasi@reka.example.my</a>.</p>
            <p><strong>EN:</strong> Access, correction, withdrawal of consent, and data portability. To request, contact <a href="mailto:privasi@reka.example.my" class="text-[#1B5E3F] underline">privasi@reka.example.my</a>.</p>
        </div>
    </section>
@endsection
