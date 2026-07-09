@extends('layouts.public')

@section('title', 'Terima kasih — REKA')

@section('content')
    <section class="mx-auto max-w-xl px-4 py-24 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#1B5E3F]/10">
            <svg class="h-8 w-8 text-[#1B5E3F]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </div>
        <h1 class="mt-6 text-3xl font-bold text-[#0F3D27]">Terima kasih!</h1>
        <p class="mt-4 text-[#1A1A1A]/75">
            Permohonan anda telah kami terima. Pasukan kami akan menghubungi anda dalam masa
            <strong>2 hari bekerja</strong> untuk langkah seterusnya.
        </p>
        <a href="{{ route('landing') }}"
           class="mt-8 inline-block rounded-xl border border-[#1B5E3F]/30 px-6 py-3 text-sm font-semibold text-[#0F3D27] hover:bg-[#EFE8DC] transition">
            Kembali ke laman utama
        </a>
    </section>
@endsection
