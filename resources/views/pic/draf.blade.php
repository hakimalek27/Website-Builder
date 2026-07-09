@extends('layouts.pic')

@section('title', 'Draf Laman — ' . $project->mosque_name)

@section('content')
    <div x-data="{ mobile: false }">
        {{-- Banner kekal --}}
        <div class="mb-4 flex items-center justify-center gap-2 rounded-xl bg-brand-800 px-4 py-2.5 text-center text-sm font-medium text-cream">
            {!! \App\Support\Lucide::svg('Info', 2, 'h-4 w-4 shrink-0 text-gold-300') !!}
            Ini DRAF SAMPEL untuk semakan — laman sebenar akan dibina selepas kelulusan.
        </div>

        {{-- Toolbar --}}
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <span class="flex items-center gap-1.5 text-sm text-ink/55">
                {!! \App\Support\Lucide::svg('Clock', 1.75, 'h-4 w-4') !!}
                Versi draf · {{ $generation->created_at->format('d/m/Y H:i') }}
            </span>
            <div class="inline-flex gap-1 rounded-full bg-sand/70 p-1">
                <button type="button" @click="mobile = false"
                    :class="!mobile ? 'bg-white text-brand-700 shadow-xs' : 'text-ink/50'"
                    class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition">
                    {!! \App\Support\Lucide::svg('Monitor', 2, 'h-3.5 w-3.5') !!} Desktop
                </button>
                <button type="button" @click="mobile = true"
                    :class="mobile ? 'bg-white text-brand-700 shadow-xs' : 'text-ink/50'"
                    class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition">
                    {!! \App\Support\Lucide::svg('Smartphone', 2, 'h-3.5 w-3.5') !!} Mobile
                </button>
            </div>
        </div>

        {{-- Bingkai browser + iframe draf (sandbox) --}}
        <div class="overflow-hidden rounded-2xl border border-sand bg-white shadow-lift">
            <div class="flex items-center gap-2 border-b border-sand bg-[#F3EFE8] px-4 py-2.5">
                <span class="h-2.5 w-2.5 rounded-full bg-[#E4654A]"></span>
                <span class="h-2.5 w-2.5 rounded-full bg-[#E3B341]"></span>
                <span class="h-2.5 w-2.5 rounded-full bg-[#54A362]"></span>
                <span class="mx-auto flex items-center gap-1.5 rounded-md bg-white px-4 py-1 text-xs text-ink/40">
                    {!! \App\Support\Lucide::svg('Lock', 2, 'h-3 w-3') !!} pratonton-draf
                </span>
            </div>
            <div class="flex justify-center bg-ink/5">
                <iframe src="{{ route('pic.draf.raw', ['token' => $token, 'generation' => $generation->id]) }}"
                    sandbox=""
                    :style="mobile ? 'width:390px' : 'width:100%'"
                    style="height:70vh;border:0;transition:width .2s"
                    title="Draf laman"></iframe>
            </div>
        </div>

        {{-- Bar tindakan --}}
        @unless ($project->isFrozen())
            <div class="mt-4 grid gap-2 sm:grid-cols-4">
                <a href="{{ route('pic.lulus', ['token' => $token]) }}"
                    class="btn btn-primary">
                    {!! \App\Support\Lucide::svg('Check', 2.5, 'h-4 w-4') !!} Luluskan
                </a>
                <form method="POST" action="{{ route('pic.tweak.reka.render', ['token' => $token]) }}">@csrf
                    <button type="submit" class="btn btn-outline btn-block">Tweak Reka Bentuk (Percuma)</button>
                </form>
                <a href="{{ route('pic.tweak.kandungan', ['token' => $token]) }}"
                    class="btn btn-outline">Tweak Kandungan (AI — baki {{ $project->remainingAiQuota() }})</a>
                <a href="{{ route('pic.status', ['token' => $token]) }}"
                    class="btn btn-ghost">
                    {!! \App\Support\Lucide::svg('Send', 2, 'h-4 w-4') !!} Hantar Nota
                </a>
            </div>
        @else
            <div class="mt-4 flex items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">
                {!! \App\Support\Lucide::svg('CircleCheck', 2, 'h-5 w-5 shrink-0 text-amber-500') !!}
                Draf telah diluluskan — hubungi kami untuk sebarang perubahan.
            </div>
        @endunless

        @if (session('error'))
            <div class="mt-3 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                {!! \App\Support\Lucide::svg('TriangleAlert', 2, 'h-5 w-5 shrink-0 text-red-500') !!}{{ session('error') }}
            </div>
        @endif
    </div>
@endsection
