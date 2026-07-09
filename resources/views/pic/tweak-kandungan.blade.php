@extends('layouts.pic')

@section('title', 'Tweak Kandungan — ' . $project->mosque_name)

@section('content')
    <span class="eyebrow">Guna AI · kuota berbaki {{ $project->remainingAiQuota() }}</span>
    <h1 class="mt-3 font-display text-3xl font-bold text-brand-800">Tweak Kandungan</h1>
    <p class="mt-2 max-w-xl text-sm/relaxed text-ink/65">Terangkan apa yang perlu diubah. Setiap penjanaan semula menggunakan satu kuota AI.</p>

    @if (session('error'))
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {!! \App\Support\Lucide::svg('TriangleAlert', 2, 'h-5 w-5 shrink-0 text-red-500') !!}{{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {!! \App\Support\Lucide::svg('TriangleAlert', 2, 'h-5 w-5 shrink-0 text-red-500') !!}{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('pic.tweak.kandungan.submit', ['token' => $token]) }}" class="mt-6 space-y-5">
        @csrf
        <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-sand">
            <label class="label">Bahagian untuk diubah</label>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ([
                    'nada' => 'Nada penulisan',
                    'tajuk_hero' => 'Tajuk hero',
                    'perenggan_tentang' => 'Perenggan tentang',
                    'ringkasan_khidmat' => 'Ringkasan perkhidmatan',
                    'ringkasan_fasiliti' => 'Ringkasan fasiliti',
                    'lain' => 'Lain-lain',
                ] as $val => $label)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-sand px-3.5 py-2.5 text-sm transition has-[:checked]:border-brand-600/40 has-[:checked]:bg-brand-50">
                        <input type="checkbox" name="categories[]" value="{{ $val }}" class="h-4 w-4 rounded accent-brand-600">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-sand">
            <label for="tweak-message" class="label">Arahan <span class="text-red-600">*</span></label>
            <textarea id="tweak-message" name="message" rows="4" maxlength="600" required
                placeholder="Terangkan dengan jelas apa yang perlu diubah" class="input"></textarea>
        </div>

        <x-ui.button type="submit" variant="primary" size="lg">
            {!! \App\Support\Lucide::svg('Sparkles', 2, 'h-5 w-5') !!}
            Hantar &amp; Jana Semula (AI)
        </x-ui.button>
    </form>
@endsection
