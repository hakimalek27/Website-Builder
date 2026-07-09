{{-- Langkah 0 — Jenis Masjid & Titik Mula (§6 L0) --}}
<div>
    <label class="block text-sm font-semibold text-ink">Jenis masjid / surau <span class="text-red-600">*</span></label>
    <div class="mt-3 grid gap-3 sm:grid-cols-3">
        @foreach ([
            ['surau_ringkas', 'Surau / Masjid Ringkas', 'Laman padat 5–7 halaman — waktu solat, aktiviti, infaq, hubungi', '5–7 halaman', 'Building'],
            ['masjid_kariah', 'Masjid Kariah', 'Laman komuniti penuh — kelas, khidmat kariah, galeri (spt mamkl.my)', '12–18 halaman', 'Users'],
            ['masjid_besar', 'Masjid Besar', 'Laman korporat + pelawat — organisasi penuh, tempahan, dwibahasa (spt masjidwilayah.gov.my)', '20+ halaman', 'Landmark'],
        ] as [$value, $title, $desc, $pages, $icon])
            <label class="relative cursor-pointer">
                <input type="radio" wire:model.live="data.tier" value="{{ $value }}" class="peer sr-only">
                <div class="h-full rounded-2xl border-2 border-sand bg-white p-4 transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20 hover:border-brand-600/40">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600/10 text-brand-600">
                        {!! \App\Support\Lucide::svg($icon, 1.75, 'w-6 h-6') !!}
                    </div>
                    <h3 class="mt-3 font-semibold text-brand-800">{{ $title }}</h3>
                    <p class="mt-1 text-xs leading-relaxed text-ink/65">{{ $desc }}</p>
                    <span class="mt-2 inline-block text-xs font-medium text-gold-700">{{ $pages }}</span>
                </div>
            </label>
        @endforeach
    </div>
    @error('data.tier') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

    <div class="mt-8 rounded-xl border border-sand bg-cream p-4">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" wire:model.live="data.is_gov" class="mt-0.5 h-5 w-5 rounded accent-brand-600">
            <span>
                <span class="block text-sm font-semibold text-ink">Masjid kerajaan / akan guna domain .gov.my?</span>
                <span class="block mt-1 text-xs text-ink/60">
                    Jika Ya: pek pematuhan (Privasi, Keselamatan, Piagam, Hakcipta), dwibahasa &amp;
                    maklumat korporat akan dihidupkan secara automatik.
                </span>
            </span>
        </label>
    </div>
</div>
