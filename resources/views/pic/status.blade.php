@extends('layouts.pic')

@section('title', 'Status — ' . $project->mosque_name)

@php
    // Petakan 12 status enum → 5 pencapaian mesra-PIC (§4.2). §Fasa 16: label ikut mod.
    $milestones = ($templateMode ?? false) ? [
        ['label' => 'Dijemput', 'icon' => 'Mail', 'states' => ['invited']],
        ['label' => 'Sedang Diisi', 'icon' => 'BookOpen', 'states' => ['in_progress']],
        ['label' => 'Telah Dihantar', 'icon' => 'Send', 'states' => ['submitted', 'draft_ready']],
        ['label' => 'Dalam Pembinaan', 'icon' => 'Landmark', 'states' => ['approved', 'handover_exported', 'in_build', 'in_review']],
        ['label' => 'Laman Live', 'icon' => 'Sparkles', 'states' => ['live']],
    ] : [
        ['label' => 'Dijemput', 'icon' => 'Mail', 'states' => ['invited']],
        ['label' => 'Sedang Diisi', 'icon' => 'BookOpen', 'states' => ['in_progress']],
        ['label' => 'Draf Sedia', 'icon' => 'Sparkles', 'states' => ['submitted', 'draft_ready']],
        ['label' => 'Diluluskan', 'icon' => 'HeartHandshake', 'states' => ['approved', 'handover_exported']],
        ['label' => 'Laman Dibina', 'icon' => 'Landmark', 'states' => ['in_build', 'in_review', 'live']],
    ];
    $current = $project->status->value;
    $currentIndex = -1;
    foreach ($milestones as $i => $m) {
        if (in_array($current, $m['states'], true)) {
            $currentIndex = $i;
            break;
        }
    }
    $isNegative = in_array($current, ['cancelled', 'expired', 'archived'], true);
@endphp

@section('content')
    <span class="eyebrow">Ruang kerja PIC</span>
    <h1 class="mt-3 font-display text-3xl font-bold text-brand-800">Status Projek</h1>

    @if (session('success'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-brand-600/20 bg-brand-50 p-3 text-sm text-brand-800">
            {!! \App\Support\Lucide::svg('CircleCheck', 2, 'h-5 w-5 text-brand-600') !!}{{ session('success') }}
        </div>
    @endif

    {{-- Kad status semasa --}}
    <div class="mt-4 flex items-center gap-4 rounded-3xl bg-gradient-to-br from-brand-800 to-brand-950 p-6 text-cream shadow-soft">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white/10 text-gold-300">
            {!! \App\Support\Lucide::svg($isNegative ? 'Info' : ($milestones[$currentIndex]['icon'] ?? 'Info'), 1.75, 'h-6 w-6') !!}
        </span>
        <div>
            <p class="text-xs tracking-wider text-cream/60 uppercase">Status semasa</p>
            <p class="font-display text-2xl font-bold text-cream">{{ $project->status->label() }}</p>
        </div>
    </div>

    {{-- §Fasa 16 — templat rujukan PIC (mod templat) --}}
    @if (($templateMode ?? false) && (($step2['template_snapshot']['name'] ?? null) || ($step2['template_custom_url'] ?? null)))
        <div class="mt-4 rounded-3xl bg-white p-5 shadow-soft ring-1 ring-sand">
            <p class="text-xs tracking-wider text-ink/45 uppercase">Templat rujukan anda</p>
            @if ($step2['template_snapshot']['name'] ?? null)
                <p class="mt-1 font-semibold text-brand-800">{{ $step2['template_snapshot']['name'] }}</p>
                @if ($step2['template_snapshot']['url'] ?? null)
                    <a href="{{ $step2['template_snapshot']['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-brand-700 underline">Lihat rujukan ↗</a>
                @endif
            @endif
            @if ($step2['template_custom_url'] ?? null)
                <p class="mt-1 text-sm break-all text-ink/70">Pautan contoh anda:
                    <a href="{{ $step2['template_custom_url'] }}" target="_blank" rel="noopener noreferrer" class="text-brand-700 underline">{{ $step2['template_custom_url'] }}</a>
                </p>
            @endif
            <p class="mt-2 text-xs text-ink/50">Pasukan REKA sedang membina laman anda berdasarkan rujukan ini &amp; nota yang anda beri.</p>
        </div>
    @endif

    {{-- Timeline pencapaian --}}
    @unless ($isNegative)
        <div class="mt-6 rounded-3xl bg-white p-6 shadow-soft ring-1 ring-sand">
            <ol class="space-y-1">
                @foreach ($milestones as $i => $m)
                    @php $state = $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'current' : 'future'); @endphp
                    <li class="relative flex items-center gap-4 pb-6 last:pb-0">
                        @unless ($loop->last)
                            <span @class(['absolute top-10 left-[19px] h-full w-px', 'bg-brand-600' => $state === 'done', 'bg-sand' => $state !== 'done'])></span>
                        @endunless
                        <span @class([
                            'relative z-10 grid h-10 w-10 shrink-0 place-items-center rounded-full',
                            'bg-brand-600 text-white' => $state === 'done',
                            'bg-gold-400 text-brand-900 ring-4 ring-gold-400/20' => $state === 'current',
                            'bg-sand text-ink/40' => $state === 'future',
                        ])>
                            @if ($state === 'done')
                                {!! \App\Support\Lucide::svg('Check', 2.5, 'h-5 w-5') !!}
                            @else
                                {!! \App\Support\Lucide::svg($m['icon'], 1.75, 'h-5 w-5') !!}
                            @endif
                        </span>
                        <span @class(['font-medium', 'text-brand-800' => $state !== 'future', 'text-ink/40' => $state === 'future'])>
                            {{ $m['label'] }}
                            @if ($state === 'current')
                                <span class="ml-1 text-xs font-normal text-gold-600">· sekarang</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
    @endunless

    {{-- Thread nota --}}
    <div class="mt-8">
        <h2 class="mb-3 flex items-center gap-2 text-sm font-semibold text-brand-800">
            {!! \App\Support\Lucide::svg('Send', 1.75, 'h-4 w-4 text-brand-600') !!} Nota kepada admin
        </h2>

        @if ($notes->isNotEmpty())
            <div class="mb-4 space-y-3">
                @foreach ($notes as $note)
                    @php $isPic = $note->author === 'pic'; @endphp
                    <div class="flex {{ $isPic ? 'justify-end' : 'justify-start' }}">
                        <div @class([
                            'max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-xs',
                            'bg-brand-600 text-white' => $isPic,
                            'bg-white text-ink ring-1 ring-sand' => ! $isPic,
                        ])>
                            <p class="{{ $isPic ? 'text-white/90' : 'text-ink/80' }}">{{ $note->body }}</p>
                            <p class="mt-1.5 text-[11px] {{ $isPic ? 'text-white/60' : 'text-ink/40' }}">
                                {{ $isPic ? ($note->author_name ?? 'Anda') : 'Admin REKA' }} ·
                                {{ $note->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('pic.nota', ['token' => $token]) }}" class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-sand">
            @csrf
            <textarea name="body" rows="3" maxlength="2000" required placeholder="Tulis nota anda…" class="input"></textarea>
            <div class="mt-3 flex justify-end">
                <x-ui.button type="submit" variant="primary" size="sm">
                    {!! \App\Support\Lucide::svg('Send', 2, 'h-4 w-4') !!} Hantar Nota
                </x-ui.button>
            </div>
        </form>
    </div>
@endsection
