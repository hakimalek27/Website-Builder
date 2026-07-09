@extends('layouts.pic')

@section('title', 'Jana Draf — '.$project->mosque_name)

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-[#0F3D27]">Penjanaan Draf</h1>
        <a href="{{ route('pic.home', ['token' => $token]) }}" class="text-sm text-[#1B5E3F] hover:underline">&larr; Senarai langkah</a>
    </div>
    @livewire('pic.jana-hub', ['token' => $token], key('jana-'.$token))
@endsection
