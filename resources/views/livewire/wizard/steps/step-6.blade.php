{{-- Langkah 6 — Media & Aset (§6 L6) --}}
@php $inp = 'mt-1 w-full rounded-xl border border-sand bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/12 focus:outline-none'; @endphp
<div class="space-y-6">
    <div>
        <label class="block text-sm font-semibold">Imej hero (banner utama) <span class="text-red-600">*</span></label>
        <div class="mt-2 space-y-1.5 text-sm">
            @foreach ([
                'upload' => 'Muat naik sendiri (1–3 imej landskap, ≥1600px lebar)',
                'perlu_fotografi' => 'Perlu khidmat fotografi (dibincang; tip: cahaya terbaik ±1 jam sebelum Maghrib)',
                'stok_sementara' => 'Guna imej stok sementara sehingga gambar sebenar sedia',
            ] as $val => $label)
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.hero_mode" value="{{ $val }}" class="h-4 w-4 accent-brand-600"> {{ $label }}</label>
            @endforeach
        </div>
        @error('data.hero_mode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

        @if (($data['hero_mode'] ?? null) === 'upload')
            @php $heroFiles = $data['hero_files'] ?? []; @endphp
            <div class="mt-3">
                @if (count($heroFiles) < 3)
                    <input type="file" wire:model="files.hero" multiple accept="image/jpeg,image/png,image/webp" class="{{ $inp }} text-xs">
                    <p class="mt-1 text-xs text-ink/55">Pilih 1–3 imej landskap (≥1600px lebar). Boleh pilih beberapa sekali gus.</p>
                @else
                    <p class="text-xs text-ink/55">Maksimum 3 imej dicapai — buang satu untuk menambah yang lain.</p>
                @endif
                <div wire:loading wire:target="files.hero" class="mt-1 text-xs text-gold-700">Memuat naik…</div>
                @error('files.hero') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                @if (! empty($heroFiles))
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($heroFiles as $i => $hf)
                            <div class="relative overflow-hidden rounded-xl border border-sand" wire:key="hero-{{ $hf['asset_id'] ?? $i }}">
                                <img src="{{ route('pic.aset', ['token' => $token, 'asset' => $hf['asset_id']]) }}" alt="Imej hero {{ $i + 1 }}" class="h-24 w-full object-cover">
                                <button type="button" wire:click="removeHeroFile({{ $i }})"
                                    class="absolute top-1 right-1 grid h-6 w-6 place-items-center rounded-full bg-white/90 text-red-600 shadow transition hover:bg-white">
                                    {!! \App\Support\Lucide::svg('X', 2, 'h-3.5 w-3.5') !!}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-brand-600">✓ {{ count($heroFiles) }}/3 imej dimuat naik</p>
                @endif
            </div>
        @elseif (in_array($data['hero_mode'] ?? null, ['stok_sementara', 'perlu_fotografi'], true))
            {{-- §Fasa 15 — janji foto stok premium (bukan gradien kosong). --}}
            <div class="mt-3 rounded-xl border border-gold-300 bg-gold-50/50 p-3">
                <p class="text-xs font-semibold text-brand-700">&#10022; Foto latar hero premium disediakan automatik</p>
                <p class="mt-1 text-xs text-ink/65">
                    Draf anda akan menggunakan <b>ilustrasi latar bertema masjid/komuniti</b> yang diwarnakan sepadan
                    pakej pilihan anda — kemas &amp; berkelas, bukan latar kosong. Imej sebenar boleh dimuat naik kemudian
                    untuk laman produksi.
                </p>
            </div>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium">Video pengenalan (YouTube, pilihan)</label>
        <input type="url" wire:model.blur="data.video_url" placeholder="https://youtube.com/…" class="{{ $inp }}">
    </div>

    <p class="rounded-xl bg-cream px-3 py-2 text-xs text-ink/60">
        Semua imej dimuat naik akan di-proses semula & data EXIF (termasuk lokasi GPS) dibuang secara automatik untuk privasi.
    </p>
</div>
