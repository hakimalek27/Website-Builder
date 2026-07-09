@extends('layouts.pic')

@section('title', 'Jana Draf — ' . $project->mosque_name)

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <span class="eyebrow">Langkah 4</span>
            <h1 class="mt-3 font-display text-3xl font-bold text-brand-800">Penjanaan Draf</h1>
        </div>
        <a href="{{ route('pic.home', ['token' => $token]) }}" class="flex shrink-0 items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
            {!! \App\Support\Lucide::svg('ArrowLeft', 2, 'h-4 w-4') !!} Senarai langkah
        </a>
    </div>
    @livewire('pic.jana-hub', ['token' => $token], key('jana-' . $token))
@endsection
