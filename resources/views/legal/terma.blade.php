@extends('layouts.public')

@section('title', 'Terma Perkhidmatan — REKA')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-20">
        <div class="text-center">
            <span class="eyebrow">Perundangan</span>
            <h1 class="mt-4 font-display text-4xl font-bold text-brand-800">Terma Perkhidmatan</h1>
            <p class="mt-1 text-sm text-ink/50">Terms of Service</p>
            <p class="mt-3 text-xs text-gold-600">Ringkas dwibahasa. (Draf — untuk semakan perundangan.)</p>
        </div>

        <div class="mx-auto mt-6 h-px w-24 bg-gradient-to-r from-transparent via-gold-400 to-transparent"></div>

        <div class="mt-10 space-y-4">
            @foreach ([
                ['REKA menyediakan platform untuk mengumpul maklumat dan menjana draf sampel laman web masjid. Draf sampel BUKAN laman sebenar; laman sebenar dibina selepas kelulusan.', 'REKA provides a platform to collect information and generate sample drafts of a mosque website. The sample draft is NOT the final website; the actual site is built after approval.'],
                ['PIC mengesahkan bahawa dia diberi kuasa oleh masjid/AJK dan bahawa maklumat yang diberikan adalah benar. Tanggungjawab ke atas kandungan akhir dan pematuhan Akta Komunikasi & Multimedia 1998 kekal pada pihak masjid.', 'The PIC confirms they are authorised by the mosque/committee and that the information provided is accurate. Responsibility for final content and compliance with the Communications & Multimedia Act 1998 remains with the mosque.'],
                ['Caj perkhidmatan diuruskan di luar platform (invois manual). Font menggunakan Google Fonts (lesen bebas); imej stok mesti bersumber berlesen.', 'Service charges are handled off-platform (manual invoicing). Fonts use Google Fonts (free license); stock images must be from licensed sources.'],
            ] as $i => [$bm, $en])
                <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-sand">
                    <div class="flex gap-4">
                        <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 font-display text-sm font-bold text-brand-600">{{ $i + 1 }}</span>
                        <div>
                            <p class="text-sm/relaxed text-ink/70"><strong class="font-semibold text-ink/80">BM:</strong> {{ $bm }}</p>
                            <p class="mt-2 text-sm/relaxed text-ink/60"><strong class="font-semibold text-ink/70">EN:</strong> {{ $en }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
