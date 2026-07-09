@extends('layouts.pic')

@section('title', 'Status — '.$project->mosque_name)

@section('content')
    <h1 class="text-2xl font-bold text-[#0F3D27]">Status Projek</h1>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-[#1B5E3F]/30 bg-[#1B5E3F]/5 p-3 text-sm text-[#0F3D27]">{{ session('success') }}</div>
    @endif

    <div class="mt-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-[#EFE8DC]">
        <p class="text-sm text-[#1A1A1A]/60">Status semasa</p>
        <p class="mt-1 text-lg font-semibold text-[#0F3D27]">{{ $project->status->label() }}</p>
    </div>

    {{-- Thread nota (dilengkapkan Fasa 9) --}}
    <div class="mt-6">
        <h2 class="text-sm font-semibold text-[#0F3D27]">Nota kepada admin</h2>
        <form method="POST" action="{{ route('pic.nota', ['token' => $token]) }}" class="mt-2">
            @csrf
            <textarea name="body" rows="3" maxlength="2000" required placeholder="Tulis nota anda…" class="w-full rounded-lg border border-[#EFE8DC] px-3 py-2 text-sm"></textarea>
            <button type="submit" class="mt-2 rounded-xl bg-[#1B5E3F] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0F3D27]">Hantar Nota</button>
        </form>
    </div>
@endsection
