@extends('layouts.pic')

@section('title', 'Borang laman — ' . $project->mosque_name)

@section('content')
    <div class="mb-6">
        <span class="eyebrow">Ruang kerja PIC</span>
        <h1 class="mt-3 font-display text-3xl font-bold text-brand-800">Assalamualaikum &amp; Selamat Datang</h1>
        <p class="mt-2 text-ink/65">
            Borang untuk laman web <strong class="text-ink/80">{{ $project->mosque_name }}</strong>. Isi mengikut
            keselesaan — setiap langkah disimpan automatik dan boleh disambung bila-bila.
        </p>
    </div>

    {{-- Kad kemajuan --}}
    <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-sand">
        <div class="flex flex-col items-center gap-6 p-6 sm:flex-row sm:gap-8 sm:p-8">
            <x-ui.progress-ring :value="$progress['percent']" class="shrink-0 text-brand-600">
                <span class="mt-0.5 text-[11px] font-medium text-ink/50">selesai</span>
            </x-ui.progress-ring>
            <div class="flex-1 text-center sm:text-left">
                <p class="text-sm text-ink/60">
                    <strong class="font-semibold text-brand-800">{{ $progress['completed'] }}</strong> daripada
                    {{ $progress['total'] }} langkah selesai
                </p>
                <h2 class="mt-1 font-display text-2xl font-bold text-brand-800">
                    @if ($progress['completed'] === 0)
                        Jom mulakan borang anda
                    @elseif ($progress['percent'] === 100)
                        Semua langkah selesai! 🎉
                    @else
                        Teruskan di mana anda berhenti
                    @endif
                </h2>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-start">
                    <x-ui.button :href="route('pic.step', ['token' => $token, 'step' => $progress['resume_step']])" variant="primary">
                        @if ($progress['completed'] === 0)
                            Mula Isi Borang
                        @else
                            Sambung di Langkah {{ $progress['resume_step'] }}
                        @endif
                        {!! \App\Support\Lucide::svg('ArrowRight', 2, 'h-4 w-4') !!}
                    </x-ui.button>
                    <x-ui.button :href="route('pic.semak', ['token' => $token])" variant="outline">Semak &amp; Hantar</x-ui.button>
                </div>
            </div>
        </div>
    </div>

    {{-- Pintasan pasca-hantar --}}
    @if (! in_array($project->status, [\App\Enums\ProjectStatus::Invited, \App\Enums\ProjectStatus::InProgress], true))
        @php $templateMode = \App\Services\DraftGenerationService::pipelineMode() === 'template'; @endphp
        <div class="mt-8">
            <h3 class="mb-3 px-1 text-xs font-semibold tracking-wider text-ink/45 uppercase">{{ $templateMode ? 'Selepas hantar' : 'Draf laman anda' }}</h3>
            <div class="grid gap-3 sm:grid-cols-3">
                @unless ($templateMode)
                <a href="{{ route('pic.jana', ['token' => $token]) }}"
                    class="group flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-xs ring-1 ring-sand transition-all hover:-translate-y-0.5 hover:shadow-soft hover:ring-brand-600/30">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gold-400/15 text-gold-600">
                        {!! \App\Support\Lucide::svg('Sparkles', 1.75, 'h-5 w-5') !!}
                    </span>
                    <span class="flex-1 text-sm font-semibold text-brand-800">Jana Draf</span>
                    {!! \App\Support\Lucide::svg('ChevronRight', 2, 'h-4 w-4 text-ink/25 transition group-hover:text-brand-600') !!}
                </a>
                @if ($project->latestDraft)
                    <a href="{{ route('pic.draf', ['token' => $token, 'generation' => $project->latestDraft->id]) }}"
                        class="group flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-xs ring-1 ring-sand transition-all hover:-translate-y-0.5 hover:shadow-soft hover:ring-brand-600/30">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600">
                            {!! \App\Support\Lucide::svg('FileText', 1.75, 'h-5 w-5') !!}
                        </span>
                        <span class="flex-1 text-sm font-semibold text-brand-800">Lihat Draf Terkini</span>
                        {!! \App\Support\Lucide::svg('ChevronRight', 2, 'h-4 w-4 text-ink/25 transition group-hover:text-brand-600') !!}
                    </a>
                @endif
                @endunless
                <a href="{{ route('pic.status', ['token' => $token]) }}"
                    class="group flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-xs ring-1 ring-sand transition-all hover:-translate-y-0.5 hover:shadow-soft hover:ring-brand-600/30">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600">
                        {!! \App\Support\Lucide::svg('MessageSquare', 1.75, 'h-5 w-5') !!}
                    </span>
                    <span class="flex-1 text-sm font-semibold text-brand-800">Status &amp; Nota</span>
                    {!! \App\Support\Lucide::svg('ChevronRight', 2, 'h-4 w-4 text-ink/25 transition group-hover:text-brand-600') !!}
                </a>
            </div>
        </div>
    @endif

    {{-- Senarai 10 langkah --}}
    <div class="mt-8">
        <h3 class="mb-3 px-1 text-xs font-semibold tracking-wider text-ink/45 uppercase">Langkah-langkah</h3>
        <div class="space-y-2">
            @foreach ($progress['steps'] as $step)
                <a href="{{ route('pic.step', ['token' => $token, 'step' => $step['index']]) }}"
                    class="group flex items-center gap-4 rounded-2xl bg-white px-4 py-3.5 shadow-xs ring-1 ring-sand transition-all hover:-translate-y-0.5 hover:shadow-soft hover:ring-brand-600/30">
                    <span @class([
                        'grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-semibold transition',
                        'bg-brand-600 text-white' => $step['status'] === 'complete',
                        'bg-gold-400/25 text-gold-700' => $step['status'] === 'partial',
                        'bg-sand text-ink/45' => $step['status'] === 'empty',
                    ])>
                        @if ($step['status'] === 'complete')
                            {!! \App\Support\Lucide::svg('Check', 2.5, 'h-4 w-4') !!}
                        @else
                            {{ $step['index'] }}
                        @endif
                    </span>
                    <span class="flex-1">
                        <span class="block font-medium text-ink">{{ $step['title'] }}</span>
                        <span class="block text-xs text-ink/50">{{ $step['subtitle'] }}</span>
                    </span>
                    <span @class([
                        'chip shrink-0',
                        'bg-brand-50 text-brand-700' => $step['status'] === 'complete',
                        'bg-gold-400/15 text-gold-700' => $step['status'] === 'partial',
                        'bg-sand/60 text-ink/45' => $step['status'] === 'empty',
                    ])>
                        @switch($step['status'])
                            @case('complete') Selesai @break
                            @case('partial') Separa @break
                            @default Belum
                        @endswitch
                    </span>
                    <span class="text-ink/25 transition group-hover:translate-x-0.5 group-hover:text-brand-600">
                        {!! \App\Support\Lucide::svg('ChevronRight', 2, 'h-4 w-4') !!}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@endsection
