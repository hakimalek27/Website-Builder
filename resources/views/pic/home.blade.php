@extends('layouts.pic')

@section('title', 'Borang laman — '.$project->mosque_name)

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#0F3D27]">Assalamualaikum &amp; Selamat Datang</h1>
        <p class="mt-1 text-[#1A1A1A]/70">
            Borang untuk laman web <strong>{{ $project->mosque_name }}</strong>. Anda boleh isi mengikut
            keselesaan — setiap langkah disimpan automatik dan boleh disambung bila-bila.
        </p>
    </div>

    {{-- Bar progres keseluruhan --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-[#EFE8DC]">
        <div class="flex items-center justify-between text-sm">
            <span class="font-medium text-[#0F3D27]">Kemajuan keseluruhan</span>
            <span class="text-[#1A1A1A]/60">{{ $progress['completed'] }}/{{ $progress['total'] }} langkah selesai</span>
        </div>
        <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-[#EFE8DC]">
            <div class="h-full rounded-full bg-[#1B5E3F] transition-all" style="width: {{ $progress['percent'] }}%"></div>
        </div>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('pic.step', ['token' => $token, 'step' => $progress['resume_step']]) }}"
               class="inline-block rounded-xl bg-[#1B5E3F] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0F3D27] transition">
                @if ($progress['completed'] === 0)
                    Mula Isi Borang
                @else
                    Sambung di Langkah {{ $progress['resume_step'] }}
                @endif
            </a>
            <a href="{{ route('pic.semak', ['token' => $token]) }}"
               class="inline-block rounded-xl border border-[#1B5E3F]/30 px-6 py-3 text-sm font-semibold text-[#0F3D27] hover:bg-[#EFE8DC] transition">
                Semak &amp; Hantar
            </a>
        </div>
    </div>

    {{-- Senarai 10 langkah dengan status --}}
    <div class="mt-6 space-y-2">
        @foreach ($progress['steps'] as $step)
            <a href="{{ route('pic.step', ['token' => $token, 'step' => $step['index']]) }}"
               class="flex items-center gap-4 rounded-xl bg-white px-4 py-3 ring-1 ring-[#EFE8DC] hover:ring-[#1B5E3F]/40 transition">
                <span @class([
                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold',
                    'bg-[#1B5E3F] text-white' => $step['status'] === 'complete',
                    'bg-[#C9A961]/30 text-[#8C6D2F]' => $step['status'] === 'partial',
                    'bg-[#EFE8DC] text-[#1A1A1A]/50' => $step['status'] === 'empty',
                ])>
                    @if ($step['status'] === 'complete')
                        ✓
                    @else
                        {{ $step['index'] }}
                    @endif
                </span>
                <span class="flex-1">
                    <span class="block font-medium text-[#1A1A1A]">{{ $step['title'] }}</span>
                    <span class="block text-xs text-[#1A1A1A]/55">{{ $step['subtitle'] }}</span>
                </span>
                <span @class([
                    'text-xs font-medium',
                    'text-[#1B5E3F]' => $step['status'] === 'complete',
                    'text-[#8C6D2F]' => $step['status'] === 'partial',
                    'text-[#1A1A1A]/40' => $step['status'] === 'empty',
                ])>
                    @switch($step['status'])
                        @case('complete') Selesai @break
                        @case('partial') Separa @break
                        @default Belum
                    @endswitch
                </span>
            </a>
        @endforeach
    </div>
@endsection
