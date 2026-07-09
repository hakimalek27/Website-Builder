@extends('layouts.pic')

@section('title', 'Tweak Reka Bentuk — ' . $project->mosque_name)

@section('content')
    <span class="eyebrow">Percuma · tiada AI</span>
    <h1 class="mt-3 font-display text-3xl font-bold text-brand-800">Tweak Reka Bentuk</h1>
    <p class="mt-2 max-w-xl text-sm/relaxed text-ink/65">
        Ubah pakej/warna/font/susun atur di
        <a href="{{ route('pic.step', ['token' => $token, 'step' => 2]) }}" class="font-medium text-brand-600 underline">Langkah 2</a>,
        kemudian tekan "Render Semula". Tiada AI dan tiada kuota AI digunakan.
    </p>

    @if (session('error'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {!! \App\Support\Lucide::svg('TriangleAlert', 2, 'h-5 w-5 shrink-0 text-red-500') !!}{{ session('error') }}
        </div>
    @endif

    <div class="mt-6 rounded-2xl bg-white p-6 shadow-soft ring-1 ring-sand">
        <div class="flex items-center justify-between text-sm">
            <span class="flex items-center gap-2 font-medium text-brand-800">
                {!! \App\Support\Lucide::svg('Sparkles', 1.75, 'h-4 w-4 text-gold-500') !!} Render reka bentuk digunakan
            </span>
            <span class="font-semibold text-gold-600">{{ $project->quota_design_used }}/5</span>
        </div>
        <x-ui.progress :value="(int) round($project->quota_design_used / 5 * 100)" tone="gold" class="mt-2 h-1.5" />

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <x-ui.button :href="route('pic.step', ['token' => $token, 'step' => 2])" variant="outline">
                {!! \App\Support\Lucide::svg('Pencil', 2, 'h-4 w-4') !!} Edit Reka Bentuk
            </x-ui.button>
            <form method="POST" action="{{ route('pic.tweak.reka.render', ['token' => $token]) }}">@csrf
                <button type="submit" @disabled($project->quota_design_used >= 5) class="btn btn-primary btn-block disabled:opacity-40">
                    Render Semula (Percuma)
                </button>
            </form>
        </div>
    </div>
@endsection
