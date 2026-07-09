@extends('layouts.public')

@section('title', 'Daftar Minat — REKA')

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
        <div class="grid items-stretch gap-8 lg:grid-cols-2">
            {{-- Panel pitch (desktop) --}}
            <div class="relative hidden overflow-hidden rounded-3xl bg-brand-950 p-10 text-cream lg:flex lg:flex-col">
                <div class="bg-pattern-islamic absolute inset-0 opacity-40" aria-hidden="true"></div>
                <div class="absolute -bottom-20 -left-16 h-72 w-72 rounded-full bg-gold-500/15 blur-[100px]" aria-hidden="true"></div>
                <div class="relative flex h-full flex-col">
                    <span class="eyebrow-dark w-fit">Langkah pertama</span>
                    <h2 class="mt-6 font-display text-4xl leading-tight font-bold">
                        Mulakan perjalanan laman masjid anda
                    </h2>
                    <p class="mt-4 text-cream/70">
                        Isi borang ringkas ini. Tiada bayaran, tiada komitmen — kami hubungi anda dalam 2 hari bekerja.
                    </p>
                    <ul class="mt-8 space-y-4 text-sm">
                        @foreach ([
                            'Reka bentuk tersendiri, bukan template seragam',
                            'Domain & jenama masjid anda sendiri',
                            'Kandungan penuh — infaq, kelas, kariah, galeri',
                            'Anda semak & luluskan sebelum laman dibina',
                        ] as $benefit)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-gold-400 text-brand-900">
                                    {!! \App\Support\Lucide::svg('Check', 2.5, 'h-3 w-3') !!}
                                </span>
                                <span class="text-cream/80">{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-auto flex items-center gap-3 border-t border-white/10 pt-6">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-gold-300">
                            {!! \App\Support\Lucide::svg('Clock', 1.75, 'h-5 w-5') !!}
                        </span>
                        <p class="text-sm text-cream/70">Maklum balas dalam <strong class="text-cream">2 hari bekerja</strong></p>
                    </div>
                </div>
            </div>

            {{-- Kad borang --}}
            <div class="rounded-3xl bg-white p-7 shadow-lift ring-1 ring-sand sm:p-9">
                <h1 class="font-display text-3xl font-bold text-brand-800">Daftar Minat</h1>
                <p class="mt-2 text-sm text-ink/60">
                    Isi maklumat ringkas di bawah. Ruangan bertanda <span class="text-red-600">*</span> adalah wajib.
                </p>

                @if ($errors->any())
                    <div class="mt-6 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <span class="mt-0.5 shrink-0 text-red-500">{!! \App\Support\Lucide::svg('TriangleAlert', 2, 'h-5 w-5') !!}</span>
                        <div>
                            <p class="font-semibold">Sila betulkan ralat berikut:</p>
                            <ul class="mt-1.5 list-disc space-y-0.5 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('minat.store') }}" class="mt-7 space-y-5">
                    @csrf

                    {{-- Honeypot (§5.1) — mesti kekal kosong; disembunyikan dari pengguna sebenar. --}}
                    <div class="hidden" aria-hidden="true">
                        <label>Jangan isi ruangan ini
                            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                        </label>
                    </div>

                    <div>
                        <label for="org_type" class="label">Jenis organisasi <span class="text-red-600">*</span></label>
                        <select id="org_type" name="org_type" required class="input">
                            <option value="">— Pilih —</option>
                            @foreach (['masjid' => 'Masjid', 'surau' => 'Surau', 'ngo' => 'Pertubuhan / NGO'] as $ov => $ol)
                                <option value="{{ $ov }}" @selected(old('org_type') === $ov)>{{ $ol }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mosque_name" class="label">Nama masjid / surau / pertubuhan <span class="text-red-600">*</span></label>
                        <input id="mosque_name" name="mosque_name" type="text" required maxlength="150"
                            value="{{ old('mosque_name') }}"
                            placeholder="Cth: Masjid Al-Muttaqin Wangsa Melawati"
                            class="input">
                    </div>

                    <div>
                        <label for="state" class="label">Negeri <span class="text-red-600">*</span></label>
                        <select id="state" name="state" required class="input">
                            <option value="">— Pilih negeri —</option>
                            @foreach ($states as $state)
                                <option value="{{ $state }}" @selected(old('state') === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="pic_name" class="label">Nama wakil (PIC) <span class="text-red-600">*</span></label>
                            <input id="pic_name" name="pic_name" type="text" required maxlength="100"
                                value="{{ old('pic_name') }}" class="input">
                        </div>
                        <div>
                            <label for="pic_phone" class="label">Nombor telefon <span class="text-red-600">*</span></label>
                            <input id="pic_phone" name="pic_phone" type="tel" required
                                value="{{ old('pic_phone') }}" placeholder="0123456789" class="input">
                            <p class="field-hint">Format 01x tanpa tanda sengkang.</p>
                        </div>
                    </div>

                    <div>
                        <label for="pic_email" class="label">E-mel (pilihan)</label>
                        <input id="pic_email" name="pic_email" type="email" maxlength="150"
                            value="{{ old('pic_email') }}" class="input">
                    </div>

                    <div>
                        <label for="current_website" class="label">Laman web sedia ada (pilihan)</label>
                        <input id="current_website" name="current_website" type="url" maxlength="200"
                            value="{{ old('current_website') }}" placeholder="https://…" class="input">
                    </div>

                    <div>
                        <label for="notes" class="label">Catatan (pilihan)</label>
                        <textarea id="notes" name="notes" rows="3" maxlength="500" class="input">{{ old('notes') }}</textarea>
                    </div>

                    @if (\App\Services\Turnstile::isEnabled())
                        <div class="cf-turnstile" data-sitekey="{{ config('reka.turnstile.site_key') }}"></div>
                        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                    @endif

                    <x-ui.button type="submit" variant="primary" size="lg" class="btn-block">
                        Hantar Maklumat
                        {!! \App\Support\Lucide::svg('ArrowRight', 2, 'h-5 w-5') !!}
                    </x-ui.button>
                    <p class="text-center text-xs text-ink/55">
                        Dengan menghantar, anda bersetuju maklumat diproses selaras
                        <a href="{{ route('privasi') }}" class="underline hover:text-brand-600">Notis Privasi</a> kami.
                    </p>
                </form>
            </div>
        </div>
    </section>
@endsection
