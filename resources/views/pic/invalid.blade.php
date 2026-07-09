@extends('layouts.pic')

@section('title', 'Pautan tidak sah — REKA')

@section('content')
    <div class="mx-auto max-w-lg text-center py-16">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
            <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>
        <h1 class="mt-6 text-2xl font-bold text-[#0F3D27]">Pautan tidak sah atau telah luput</h1>
        <p class="mt-4 text-[#1A1A1A]/70">
            Maaf, pautan ini tidak dapat digunakan. Ia mungkin telah luput atau tidak lagi sah.
            Sila hubungi kami untuk pautan baharu.
        </p>
        <a href="{{ route('landing') }}"
           class="mt-8 inline-block rounded-xl border border-[#1B5E3F]/30 px-6 py-3 text-sm font-semibold text-[#0F3D27] hover:bg-[#EFE8DC] transition">
            Ke laman utama
        </a>
    </div>
@endsection
