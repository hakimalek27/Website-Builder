@extends('layouts.pic')

@section('title', 'Pautan tidak sah — REKA')

@section('content')
    <div class="mx-auto max-w-lg py-16 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100 text-red-600">
            {!! \App\Support\Lucide::svg('TriangleAlert', 1.75, 'h-10 w-10') !!}
        </div>
        <h1 class="mt-8 font-display text-3xl font-bold text-brand-800">Pautan tidak sah atau telah luput</h1>
        <p class="mx-auto mt-4 max-w-md text-ink/65">
            Maaf, pautan ini tidak dapat digunakan. Ia mungkin telah luput atau tidak lagi sah.
            Sila hubungi kami untuk pautan baharu.
        </p>
        <x-ui.button :href="route('landing')" variant="outline" class="mt-8">
            {!! \App\Support\Lucide::svg('ArrowLeft', 2, 'h-4 w-4') !!}
            Ke laman utama
        </x-ui.button>
    </div>
@endsection
