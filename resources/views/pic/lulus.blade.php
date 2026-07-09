@extends('layouts.pic')

@section('title', 'Luluskan Draf — '.$project->mosque_name)

@section('content')
    <h1 class="text-2xl font-bold text-[#0F3D27]">Luluskan Draf Ini</h1>
    <p class="mt-2 text-sm text-[#1A1A1A]/70">Sila sahkan identiti anda dan berikan persetujuan sebelum meluluskan.</p>

    @if (session('error'))
        <div class="mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @php $inp = 'mt-1 w-full rounded-lg border border-[#EFE8DC] px-3 py-2 text-sm'; @endphp
    <form method="POST" action="{{ route('pic.lulus.store', ['token' => $token]) }}"
          x-data="{ confirmed: false }" @submit="if(!confirmed){ $event.preventDefault(); confirmed = confirm('Tindakan ini muktamad. Teruskan meluluskan draf?'); if(confirmed) $el.submit(); }"
          class="mt-6 space-y-4">
        @csrf
        <div class="grid gap-3 sm:grid-cols-3">
            <div><label class="block text-sm font-medium">Nama <span class="text-red-600">*</span></label>
                <input type="text" name="pic_name" value="{{ old('pic_name', $invitation?->pic_name) }}" required class="{{ $inp }}"></div>
            <div><label class="block text-sm font-medium">Jawatan <span class="text-red-600">*</span></label>
                <input type="text" name="pic_position" value="{{ old('pic_position') }}" placeholder="Setiausaha AJK" required class="{{ $inp }}"></div>
            <div><label class="block text-sm font-medium">Telefon <span class="text-red-600">*</span></label>
                <input type="tel" name="pic_phone" value="{{ old('pic_phone', $invitation?->pic_phone) }}" required class="{{ $inp }}"></div>
        </div>

        <div class="space-y-3 rounded-xl border border-[#EFE8DC] bg-[#FAF7F2] p-4">
            <label class="flex items-start gap-2.5 text-sm">
                <input type="checkbox" name="declare_authority" value="1" class="mt-0.5 text-[#1B5E3F]">
                <span>{{ __('reka.declare_authority') }}</span>
            </label>
            <label class="flex items-start gap-2.5 text-sm">
                <input type="checkbox" name="consent_pdpa" value="1" class="mt-0.5 text-[#1B5E3F]">
                <span>{{ __('reka.consent_pdpa', ['business' => config('reka.business_name')]) }}
                    <a href="{{ route('privasi') }}" target="_blank" class="text-[#1B5E3F] underline">Notis Privasi</a>.</span>
            </label>
        </div>

        <button type="submit" class="w-full rounded-xl bg-[#1B5E3F] px-6 py-3.5 text-base font-semibold text-white hover:bg-[#0F3D27]">
            Luluskan Draf Ini
        </button>
    </form>
@endsection
