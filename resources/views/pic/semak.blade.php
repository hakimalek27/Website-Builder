@extends('layouts.pic')

@section('title', 'Semak & Hantar — ' . $project->mosque_name)

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-brand-800">Semak &amp; Hantar</h1>
            <p class="mt-1 text-sm text-ink/60">Semak ringkasan sebelum menghantar untuk penjanaan draf.</p>
        </div>
        <a href="{{ route('pic.home', ['token' => $token]) }}" class="flex shrink-0 items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
            {!! \App\Support\Lucide::svg('ArrowLeft', 2, 'h-4 w-4') !!} Senarai langkah
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-brand-600/20 bg-brand-50 p-4 text-sm text-brand-800">
            <span class="text-brand-600">{!! \App\Support\Lucide::svg('CircleCheck', 2, 'h-5 w-5') !!}</span>{{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <span class="text-red-500">{!! \App\Support\Lucide::svg('TriangleAlert', 2, 'h-5 w-5') !!}</span>{{ session('error') }}
        </div>
    @endif
    @if ($alreadySubmitted)
        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">
            <span class="text-amber-500">{!! \App\Support\Lucide::svg('Info', 2, 'h-5 w-5') !!}</span>
            <span class="flex-1">Telah dihantar — anda masih boleh edit sehingga draf diluluskan.</span>
            <a href="{{ route('pic.jana', ['token' => $token]) }}"
                class="flex shrink-0 items-center gap-1 rounded-lg bg-amber-500/15 px-3 py-1.5 font-medium text-amber-800 transition hover:bg-amber-500/25">
                Pergi ke Jana Draf {!! \App\Support\Lucide::svg('ArrowRight', 2, 'h-4 w-4') !!}
            </a>
        </div>
    @endif

    {{-- Skor kelengkapan --}}
    <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-sand sm:p-8">
        <div class="flex flex-col items-center gap-6 sm:flex-row sm:gap-8">
            <x-ui.progress-ring :value="$result['score']" class="shrink-0 text-brand-600">
                <span class="mt-0.5 text-[11px] font-medium text-ink/50">lengkap</span>
            </x-ui.progress-ring>
            <div class="flex-1 text-center sm:text-left">
                <p class="font-display text-xl font-bold {{ $result['score'] === 100 ? 'text-brand-700' : 'text-gold-600' }}">
                    {{ $result['filled'] }} / {{ $result['total'] }} medan wajib terisi
                </p>
                @if ($result['score'] === 100)
                    <p class="mt-1 text-sm text-ink/60">Semua medan wajib lengkap — anda sedia untuk menghantar.</p>
                @else
                    <p class="mt-1 text-sm text-ink/60">Lengkapkan medan di bawah untuk membuka butang hantar.</p>
                @endif

                @if (! empty($result['missing']))
                    <div class="mt-4">
                        <p class="mb-2 text-xs font-semibold tracking-wide text-ink/45 uppercase">Medan wajib belum lengkap</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($result['missing'] as $m)
                                <a href="{{ route('pic.step', ['token' => $token, 'step' => $m['step']]) }}"
                                    class="chip bg-gold-400/15 text-gold-700 ring-1 ring-gold-400/25 transition hover:bg-gold-400/25">
                                    {{ $m['label'] }}
                                    <span class="text-gold-600/70">· L{{ $m['step'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ringkasan per langkah --}}
    <div class="mt-6 grid gap-2 sm:grid-cols-2">
        @foreach ($steps as $s)
            @php $has = ! empty($sections['step_' . $s['index']] ?? []); @endphp
            <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-xs ring-1 ring-sand">
                <span @class([
                    'grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-semibold',
                    'bg-brand-600 text-white' => $has,
                    'bg-sand text-ink/45' => ! $has,
                ])>
                    @if ($has)
                        {!! \App\Support\Lucide::svg('Check', 2.5, 'h-3.5 w-3.5') !!}
                    @else
                        {{ $s['index'] }}
                    @endif
                </span>
                <span class="flex-1 text-sm font-medium text-ink">{{ $s['title'] }}</span>
                <a href="{{ route('pic.step', ['token' => $token, 'step' => $s['index']]) }}"
                    class="flex items-center gap-1 text-xs font-medium text-brand-600 hover:text-brand-700">
                    {!! \App\Support\Lucide::svg('Pencil', 2, 'h-3.5 w-3.5') !!} Edit
                </a>
            </div>
        @endforeach
    </div>

    {{-- Butiran yang diisi (nilai) — bank bermask --}}
    @if (! empty($stepBlocks))
        <div class="mt-8">
            <h3 class="mb-3 px-1 text-xs font-semibold tracking-wider text-ink/45 uppercase">Butiran yang anda isi</h3>
            <div class="space-y-2">
                @foreach ($stepBlocks as $block)
                    <details class="rounded-2xl bg-white px-4 py-3 shadow-xs ring-1 ring-sand">
                        <summary class="cursor-pointer text-sm font-medium text-brand-800">{{ $block['title'] }}</summary>
                        <div class="mt-3 text-sm text-ink/75 [&_a]:text-brand-600 [&_strong]:font-semibold [&_strong]:text-ink [&_ul]:mt-1 [&_ul]:list-disc [&_ul]:space-y-0.5 [&_ul]:pl-5">
                            {!! \Illuminate\Support\Str::markdown($block['markdown']) !!}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    @endif

    @if ($maskedBank)
        <p class="mt-4 flex items-center gap-2 text-xs text-ink/50">
            {!! \App\Support\Lucide::svg('Lock', 2, 'h-3.5 w-3.5') !!}
            Nombor akaun infaq: {{ $maskedBank }} (dipaparkan penuh dalam pakej serahan sahaja).
        </p>
    @endif

    {{-- Bar hantar melekit --}}
    <div class="sticky bottom-4 z-10 mt-6">
        <form method="POST" action="{{ route('pic.submit', ['token' => $token]) }}"
            class="rounded-2xl border border-sand bg-white/85 p-3 shadow-lift backdrop-blur-md">
            @csrf
            <button type="submit" @disabled($result['score'] !== 100)
                class="btn btn-primary btn-lg btn-block disabled:opacity-40">
                @if ($result['score'] === 100)
                    Hantar Maklumat
                    {!! \App\Support\Lucide::svg('ArrowRight', 2, 'h-5 w-5') !!}
                @else
                    Lengkapkan semua medan wajib untuk hantar
                @endif
            </button>
        </form>
    </div>
@endsection
