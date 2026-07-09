@extends('layouts.pic')

@section('title', 'Luluskan Draf — ' . $project->mosque_name)

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="text-center">
            <span class="eyebrow">Langkah terakhir</span>
            <h1 class="mt-3 font-display text-3xl font-bold text-brand-800">Luluskan Draf Ini</h1>
            <p class="mt-2 text-sm text-ink/65">Sila sahkan identiti anda dan berikan persetujuan sebelum meluluskan.</p>
        </div>

        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-gold-400/30 bg-gold-400/8 p-4 text-sm text-ink/70">
            {!! \App\Support\Lucide::svg('Info', 2, 'h-5 w-5 shrink-0 text-gold-600') !!}
            <p>Setelah diluluskan, draf ini <strong class="text-ink/85">dibekukan sebagai rekod</strong> dan wizard dikunci.
                Kami akan mula membina laman sebenar untuk <strong class="text-ink/85">{{ $project->mosque_name }}</strong>.</p>
        </div>

        @if (session('error'))
            <div class="mt-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                {!! \App\Support\Lucide::svg('TriangleAlert', 2, 'h-5 w-5 shrink-0 text-red-500') !!}{{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-disc space-y-0.5 pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pic.lulus.store', ['token' => $token]) }}"
            x-data="{ confirmed: false }"
            @submit="if(!confirmed){ $event.preventDefault(); confirmed = confirm('Tindakan ini muktamad. Teruskan meluluskan draf?'); if(confirmed) $el.submit(); }"
            class="mt-6 space-y-5">
            @csrf
            <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-sand">
                <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold text-brand-800">
                    {!! \App\Support\Lucide::svg('Users', 1.75, 'h-4 w-4 text-brand-600') !!} Pengesahan identiti
                </h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="label">Nama <span class="text-red-600">*</span></label>
                        <input type="text" name="pic_name" value="{{ old('pic_name', $invitation?->pic_name) }}" required class="input">
                    </div>
                    <div>
                        <label class="label">Jawatan <span class="text-red-600">*</span></label>
                        <input type="text" name="pic_position" value="{{ old('pic_position') }}" placeholder="Setiausaha AJK" required class="input">
                    </div>
                    <div>
                        <label class="label">Telefon <span class="text-red-600">*</span></label>
                        <input type="tel" name="pic_phone" value="{{ old('pic_phone', $invitation?->pic_phone) }}" required class="input">
                    </div>
                </div>
            </div>

            <div class="space-y-3 rounded-2xl border border-gold-400/30 bg-gold-400/8 p-5">
                <label class="flex items-start gap-3 text-sm">
                    <input type="checkbox" name="declare_authority" value="1" class="mt-0.5 h-4 w-4 rounded accent-brand-600">
                    <span class="text-ink/75">{{ __('reka.declare_authority') }}</span>
                </label>
                <label class="flex items-start gap-3 text-sm">
                    <input type="checkbox" name="consent_pdpa" value="1" class="mt-0.5 h-4 w-4 rounded accent-brand-600">
                    <span class="text-ink/75">{{ __('reka.consent_pdpa', ['business' => config('reka.business_name')]) }}
                        <a href="{{ route('privasi') }}" target="_blank" class="text-brand-600 underline">Notis Privasi</a>.</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block">
                {!! \App\Support\Lucide::svg('Check', 2.5, 'h-5 w-5') !!} Luluskan Draf Ini
            </button>
        </form>
    </div>
@endsection
