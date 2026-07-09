@extends('layouts.pic')

@section('title', 'Tweak Kandungan — '.$project->mosque_name)

@section('content')
    <h1 class="text-2xl font-bold text-[#0F3D27]">Tweak Kandungan (AI)</h1>
    <p class="mt-2 text-sm text-[#1A1A1A]/70">Terangkan apa yang perlu diubah. Baki kuota AI: {{ $project->remainingAiQuota() }}.</p>

    @if (session('error'))
        <div class="mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('pic.tweak.kandungan.submit', ['token' => $token]) }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Bahagian untuk diubah</label>
            <div class="mt-2 grid gap-1.5 sm:grid-cols-2 text-sm">
                @foreach ([
                    'nada' => 'Nada penulisan', 'tajuk_hero' => 'Tajuk hero', 'perenggan_tentang' => 'Perenggan tentang',
                    'ringkasan_khidmat' => 'Ringkasan perkhidmatan', 'ringkasan_fasiliti' => 'Ringkasan fasiliti', 'lain' => 'Lain-lain',
                ] as $val => $label)
                    <label class="flex items-center gap-2"><input type="checkbox" name="categories[]" value="{{ $val }}" class="text-[#1B5E3F]"> {{ $label }}</label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium">Arahan <span class="text-red-600">*</span></label>
            <textarea name="message" rows="4" maxlength="600" required placeholder="Terangkan dengan jelas apa yang perlu diubah" class="mt-1 w-full rounded-lg border border-[#EFE8DC] px-3 py-2 text-sm"></textarea>
        </div>
        <button type="submit" class="rounded-xl bg-[#1B5E3F] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0F3D27]">Hantar & Jana Semula (AI)</button>
    </form>
@endsection
