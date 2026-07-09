@extends('layouts.pic')

@section('title', 'Tweak Reka Bentuk — '.$project->mosque_name)

@section('content')
    <h1 class="text-2xl font-bold text-[#0F3D27]">Tweak Reka Bentuk (Percuma)</h1>
    <p class="mt-2 text-sm text-[#1A1A1A]/70">
        Ubah pakej/warna/font/susun atur di <a href="{{ route('pic.step', ['token' => $token, 'step' => 2]) }}" class="text-[#1B5E3F] underline">Langkah 2</a>,
        kemudian tekan "Render Semula". Tiada AI, tiada kuota AI digunakan (had 5 render).
    </p>
    <p class="mt-1 text-sm text-[#8C6D2F]">Render reka bentuk digunakan: {{ $project->quota_design_used }}/5</p>

    @if (session('error'))
        <div class="mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="mt-6 flex gap-3">
        <a href="{{ route('pic.step', ['token' => $token, 'step' => 2]) }}" class="rounded-xl border border-[#1B5E3F]/30 px-6 py-3 text-sm font-semibold text-[#0F3D27] hover:bg-[#EFE8DC]">Edit Reka Bentuk</a>
        <form method="POST" action="{{ route('pic.tweak.reka.render', ['token' => $token]) }}">@csrf
            <button type="submit" @disabled($project->quota_design_used >= 5)
                    class="rounded-xl bg-[#1B5E3F] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0F3D27] disabled:opacity-40">
                Render Semula (Percuma)
            </button>
        </form>
    </div>
@endsection
