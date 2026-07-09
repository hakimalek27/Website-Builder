@extends('layouts.public')

@section('title', 'Daftar Minat — REKA')

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-12">
        <h1 class="text-3xl font-bold text-[#0F3D27]">Daftar Minat</h1>
        <p class="mt-2 text-[#1A1A1A]/70">
            Isi maklumat ringkas di bawah. Kami akan menghubungi anda dalam masa 2 hari bekerja.
        </p>

        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">Sila betulkan ralat berikut:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('minat.store') }}" class="mt-8 space-y-5">
            @csrf

            {{-- Honeypot (§5.1) — mesti kekal kosong; disembunyikan dari pengguna sebenar. --}}
            <div class="hidden" aria-hidden="true">
                <label>Jangan isi ruangan ini
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                </label>
            </div>

            <div>
                <label for="mosque_name" class="block text-sm font-medium text-[#1A1A1A]">Nama masjid / surau <span class="text-red-600">*</span></label>
                <input id="mosque_name" name="mosque_name" type="text" required maxlength="150"
                       value="{{ old('mosque_name') }}"
                       placeholder="Cth: Masjid Al-Muttaqin Wangsa Melawati"
                       class="mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none">
            </div>

            <div>
                <label for="state" class="block text-sm font-medium text-[#1A1A1A]">Negeri <span class="text-red-600">*</span></label>
                <select id="state" name="state" required
                        class="mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none">
                    <option value="">— Pilih negeri —</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" @selected(old('state') === $state)>{{ $state }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="pic_name" class="block text-sm font-medium text-[#1A1A1A]">Nama wakil (PIC) <span class="text-red-600">*</span></label>
                <input id="pic_name" name="pic_name" type="text" required maxlength="100"
                       value="{{ old('pic_name') }}"
                       class="mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none">
            </div>

            <div>
                <label for="pic_phone" class="block text-sm font-medium text-[#1A1A1A]">Nombor telefon <span class="text-red-600">*</span></label>
                <input id="pic_phone" name="pic_phone" type="tel" required
                       value="{{ old('pic_phone') }}"
                       placeholder="0123456789"
                       class="mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none">
                <p class="mt-1 text-xs text-[#1A1A1A]/60">Format 01x tanpa tanda sengkang.</p>
            </div>

            <div>
                <label for="pic_email" class="block text-sm font-medium text-[#1A1A1A]">E-mel (pilihan)</label>
                <input id="pic_email" name="pic_email" type="email" maxlength="150"
                       value="{{ old('pic_email') }}"
                       class="mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none">
            </div>

            <div>
                <label for="current_website" class="block text-sm font-medium text-[#1A1A1A]">Laman web sedia ada (pilihan)</label>
                <input id="current_website" name="current_website" type="url" maxlength="200"
                       value="{{ old('current_website') }}"
                       placeholder="https://…"
                       class="mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none">
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-[#1A1A1A]">Catatan (pilihan)</label>
                <textarea id="notes" name="notes" rows="3" maxlength="500"
                          class="mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none">{{ old('notes') }}</textarea>
            </div>

            @if (\App\Services\Turnstile::isEnabled())
                <div class="cf-turnstile" data-sitekey="{{ config('reka.turnstile.site_key') }}"></div>
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            @endif

            <button type="submit"
                    class="w-full rounded-xl bg-[#1B5E3F] px-6 py-3 text-base font-semibold text-white hover:bg-[#0F3D27] transition">
                Hantar Maklumat
            </button>
            <p class="text-center text-xs text-[#1A1A1A]/60">
                Dengan menghantar, anda bersetuju maklumat diproses selaras
                <a href="{{ route('privasi') }}" class="underline">Notis Privasi</a> kami.
            </p>
        </form>
    </section>
@endsection
