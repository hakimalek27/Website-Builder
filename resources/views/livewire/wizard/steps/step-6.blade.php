{{-- Langkah 6 — Media & Aset (§6 L6) --}}
@php $inp = 'mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2 text-sm focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none'; @endphp
<div class="space-y-6">
    <div>
        <label class="block text-sm font-semibold">Imej hero (banner utama) <span class="text-red-600">*</span></label>
        <div class="mt-2 space-y-1.5 text-sm">
            @foreach ([
                'upload' => 'Muat naik sendiri (1–3 imej landskap, ≥1600px lebar)',
                'perlu_fotografi' => 'Perlu khidmat fotografi (dibincang; tip: cahaya terbaik ±1 jam sebelum Maghrib)',
                'stok_sementara' => 'Guna imej stok sementara sehingga gambar sebenar sedia',
            ] as $val => $label)
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.hero_mode" value="{{ $val }}" class="text-[#1B5E3F]"> {{ $label }}</label>
            @endforeach
        </div>
        @error('data.hero_mode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

        @if (($data['hero_mode'] ?? null) === 'upload')
            <div class="mt-3">
                <input type="file" wire:model="files.hero" accept="image/jpeg,image/png,image/webp" class="{{ $inp }} text-xs">
                @if (! empty($data['hero_files'] ?? []))
                    <p class="mt-1 text-xs text-[#1B5E3F]">✓ {{ count($data['hero_files']) }} imej dimuat naik</p>
                @endif
                <div wire:loading wire:target="files.hero" class="mt-1 text-xs text-[#8C6D2F]">Memuat naik…</div>
                @error('files.hero') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium">Video pengenalan (YouTube, pilihan)</label>
        <input type="url" wire:model.blur="data.video_url" placeholder="https://youtube.com/…" class="{{ $inp }}">
    </div>

    <p class="rounded-lg bg-[#FAF7F2] px-3 py-2 text-xs text-[#1A1A1A]/60">
        Semua imej dimuat naik akan di-proses semula & data EXIF (termasuk lokasi GPS) dibuang secara automatik untuk privasi.
    </p>
</div>
