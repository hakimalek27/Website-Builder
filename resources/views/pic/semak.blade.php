@extends('layouts.pic')

@section('title', 'Semak & Hantar — '.$project->mosque_name)

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#0F3D27]">Semak &amp; Hantar</h1>
            <p class="mt-1 text-sm text-[#1A1A1A]/70">Semak ringkasan sebelum menghantar untuk penjanaan draf.</p>
        </div>
        <a href="{{ route('pic.home', ['token' => $token]) }}" class="text-sm text-[#1B5E3F] hover:underline">&larr; Senarai langkah</a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-[#1B5E3F]/30 bg-[#1B5E3F]/5 p-4 text-sm text-[#0F3D27]">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if ($alreadySubmitted)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">
            Telah dihantar — anda masih boleh edit sehingga draf diluluskan.
        </div>
    @endif

    {{-- Skor kelengkapan --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-[#EFE8DC]">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium text-[#0F3D27]">Skor kelengkapan</span>
            <span class="font-semibold {{ $result['score'] === 100 ? 'text-[#1B5E3F]' : 'text-[#8C6D2F]' }}">{{ $result['score'] }}% ({{ $result['filled'] }}/{{ $result['total'] }})</span>
        </div>
        <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-[#EFE8DC]">
            <div class="h-full rounded-full {{ $result['score'] === 100 ? 'bg-[#1B5E3F]' : 'bg-[#C9A961]' }} transition-all" style="width: {{ $result['score'] }}%"></div>
        </div>

        @if (! empty($result['missing']))
            <div class="mt-4">
                <p class="text-sm font-medium text-[#1A1A1A]">Medan wajib belum lengkap:</p>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($result['missing'] as $m)
                        <li>
                            <a href="{{ route('pic.step', ['token' => $token, 'step' => $m['step']]) }}" class="text-[#1B5E3F] hover:underline">
                                • {{ $m['label'] }} <span class="text-xs text-[#1A1A1A]/40">(Langkah {{ $m['step'] }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Ringkasan per langkah --}}
    <div class="mt-6 space-y-2">
        @foreach ($steps as $s)
            @php $has = ! empty($sections['step_'.$s['index']] ?? []); @endphp
            <div class="flex items-center gap-3 rounded-xl bg-white px-4 py-3 ring-1 ring-[#EFE8DC]">
                <span @class([
                    'flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold',
                    'bg-[#1B5E3F] text-white' => $has, 'bg-[#EFE8DC] text-[#1A1A1A]/50' => ! $has,
                ])>{{ $has ? '✓' : $s['index'] }}</span>
                <span class="flex-1 text-sm font-medium">{{ $s['title'] }}</span>
                <a href="{{ route('pic.step', ['token' => $token, 'step' => $s['index']]) }}" class="text-xs font-medium text-[#1B5E3F] hover:underline">Edit</a>
            </div>
        @endforeach
    </div>

    @if ($maskedBank)
        <p class="mt-4 text-xs text-[#1A1A1A]/50">Nombor akaun infaq: {{ $maskedBank }} (dipaparkan penuh dalam pakej serahan sahaja).</p>
    @endif

    {{-- Hantar --}}
    <div class="mt-6">
        <form method="POST" action="{{ route('pic.submit', ['token' => $token]) }}">
            @csrf
            <button type="submit" @disabled($result['score'] !== 100)
                    class="w-full rounded-xl bg-[#1B5E3F] px-6 py-3.5 text-base font-semibold text-white hover:bg-[#0F3D27] disabled:opacity-40 disabled:cursor-not-allowed transition">
                @if ($result['score'] === 100)
                    Hantar Maklumat
                @else
                    Lengkapkan semua medan wajib untuk hantar
                @endif
            </button>
        </form>
    </div>
@endsection
