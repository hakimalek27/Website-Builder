@extends('layouts.pic')

@section('title', 'Draf Laman — '.$project->mosque_name)

@section('content')
<div x-data="{ mobile: false }">
    {{-- Banner kekal --}}
    <div class="mb-3 rounded-lg bg-[#0F3D27] px-4 py-2 text-center text-sm font-medium text-white">
        Ini DRAF SAMPEL untuk semakan — laman sebenar akan dibina selepas kelulusan.
    </div>

    {{-- Toolbar --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <span class="text-sm text-[#1A1A1A]/60">Versi draf · {{ $generation->created_at->format('d/m/Y H:i') }}</span>
        <div class="flex gap-2">
            <button type="button" @click="mobile = false" :class="!mobile ? 'bg-[#1B5E3F] text-white' : 'bg-white text-[#1A1A1A]/60'" class="rounded-lg px-3 py-1.5 text-xs font-medium ring-1 ring-[#EFE8DC]">Desktop</button>
            <button type="button" @click="mobile = true" :class="mobile ? 'bg-[#1B5E3F] text-white' : 'bg-white text-[#1A1A1A]/60'" class="rounded-lg px-3 py-1.5 text-xs font-medium ring-1 ring-[#EFE8DC]">Mobile</button>
        </div>
    </div>

    {{-- iframe draf (sandbox) --}}
    <div class="flex justify-center overflow-hidden rounded-xl border border-[#EFE8DC] bg-white">
        <iframe src="{{ route('pic.draf.raw', ['token' => $token, 'generation' => $generation->id]) }}"
                sandbox=""
                :style="mobile ? 'width:390px' : 'width:100%'"
                style="height:70vh;border:0;transition:width .2s"
                title="Draf laman"></iframe>
    </div>

    {{-- Bar tindakan --}}
    @unless ($project->isFrozen())
        <div class="mt-4 grid gap-2 sm:grid-cols-4">
            <a href="{{ route('pic.lulus', ['token' => $token]) }}" class="rounded-xl bg-[#1B5E3F] px-4 py-3 text-center text-sm font-semibold text-white hover:bg-[#0F3D27]">✓ Luluskan</a>
            <form method="POST" action="{{ route('pic.tweak.reka.render', ['token' => $token]) }}">@csrf
                <button type="submit" class="w-full rounded-xl border border-[#1B5E3F]/30 px-4 py-3 text-sm font-semibold text-[#0F3D27] hover:bg-[#EFE8DC]">Tweak Reka Bentuk (Percuma)</button>
            </form>
            <a href="{{ route('pic.tweak.kandungan', ['token' => $token]) }}" class="rounded-xl border border-[#1B5E3F]/30 px-4 py-3 text-center text-sm font-semibold text-[#0F3D27] hover:bg-[#EFE8DC]">Tweak Kandungan (AI — baki {{ $project->remainingAiQuota() }})</a>
            <a href="{{ route('pic.status', ['token' => $token]) }}" class="rounded-xl border border-[#EFE8DC] px-4 py-3 text-center text-sm font-medium text-[#1A1A1A]/70 hover:bg-[#EFE8DC]">Hantar Nota</a>
        </div>
    @else
        <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
            Draf telah diluluskan — hubungi kami untuk sebarang perubahan.
        </div>
    @endunless

    @if (session('error'))
        <div class="mt-3 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
</div>
@endsection
