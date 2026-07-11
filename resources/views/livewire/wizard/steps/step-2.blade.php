{{-- Langkah 2 — Identiti & Reka Bentuk (§6 L2) --}}
<div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
    {{-- Pilihan (kiri) --}}
    <div class="space-y-6">
        {{-- Pakej reka bentuk --}}
        <div>
            <label class="block text-sm font-semibold">Pakej reka bentuk <span class="text-red-600">*</span></label>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($designPackages as $pkg)
                    <label class="relative cursor-pointer">
                        <input type="radio" wire:model.live="data.design_package" value="{{ $pkg->key }}" class="peer sr-only">
                        <div class="rounded-xl border-2 border-sand bg-white p-3 transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20 hover:border-brand-600/40">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-brand-800" style="font-family: '{{ $pkg->fonts['display'] ?? 'serif' }}', serif;">{{ $pkg->name }}</span>
                                <span class="flex gap-1">
                                    <span class="h-4 w-4 rounded-full" style="background: {{ $pkg->tokens['primary'] }}"></span>
                                    <span class="h-4 w-4 rounded-full" style="background: {{ $pkg->tokens['accent'] }}"></span>
                                    <span class="h-4 w-4 rounded-full border" style="background: {{ $pkg->tokens['bg'] }}"></span>
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-ink/55">Sesuai untuk: {{ $pkg->preview_meta['suitable_for'] ?? '—' }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('data.design_package') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Warna: ikut pakej ATAU pilih sendiri (mod custom + kawalan kontras WCAG) --}}
        <div>
            <label class="block text-sm font-semibold">Warna</label>
            <div class="mt-2 flex gap-2">
                @foreach (['pakej' => 'Ikut pakej', 'custom' => 'Pilih sendiri'] as $m => $lbl)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="data.palette_mode" value="{{ $m }}" class="peer sr-only">
                        <span class="block rounded-xl border border-sand px-3 py-1.5 text-xs transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20">{{ $lbl }}</span>
                    </label>
                @endforeach
            </div>
            @if (($data['palette_mode'] ?? 'pakej') === 'custom')
                <div class="mt-3 flex flex-wrap items-center gap-4 rounded-xl border border-sand bg-cream p-3">
                    <label class="flex items-center gap-2 text-xs font-medium">Utama
                        <input type="color" wire:model.live="data.custom_primary" class="h-8 w-12 cursor-pointer rounded border border-sand bg-white">
                    </label>
                    <label class="flex items-center gap-2 text-xs font-medium">Aksen
                        <input type="color" wire:model.live="data.custom_accent" class="h-8 w-12 cursor-pointer rounded border border-sand bg-white">
                    </label>
                    @php $derived = $this->customPalettePreview(); @endphp
                    @if ($derived)
                        <div class="flex items-center gap-1.5">
                            @foreach (['primary', 'primaryDark', 'accent', 'ink', 'bg', 'bgAlt'] as $tk)
                                <span class="h-6 w-6 rounded-md border border-black/10" style="background: {{ $derived['tokens'][$tk] ?? '#fff' }}" title="{{ $tk }}"></span>
                            @endforeach
                        </div>
                        @if ($derived['adjusted'])
                            <p class="w-full text-xs text-gold-700">⚠ Warna utama digelapkan automatik untuk kebolehbacaan (WCAG ≥ 4.5:1).</p>
                        @endif
                    @else
                        <p class="text-xs text-ink/55">Pilih warna utama &amp; aksen untuk lihat pratonton.</p>
                    @endif
                </div>
                @error('data.custom_primary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @error('data.custom_accent') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @endif
        </div>

        {{-- Pasangan font (10 pilihan A–J; kad dipapar dalam font sebenar) --}}
        <div>
            <label class="block text-sm font-semibold">Pasangan font</label>
            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                @foreach (\App\Support\FontPairs::options() as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="data.font_pair" value="{{ $key }}" class="peer sr-only">
                        <div class="rounded-xl border border-sand px-2 py-2 text-center text-xs transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20"
                             style="font-family: '{{ \App\Support\FontPairs::previewFonts($key)['display'] }}', serif;">{{ $label }}</div>
                    </label>
                @endforeach
            </div>
            <div class="mt-2">
                <label class="text-xs text-ink/60">Font Arab</label>
                <select wire:model.live="data.arabic_font" class="ml-2 rounded-xl border border-sand px-2 py-1 text-xs">
                    <option value="Amiri">Amiri</option>
                    <option value="Scheherazade New">Scheherazade New</option>
                </select>
            </div>
        </div>

        {{-- Gaya ikon --}}
        <div>
            <label class="block text-sm font-semibold">Gaya ikon</label>
            <div class="mt-2 grid grid-cols-2 gap-4">
                <div>
                    <span class="text-xs text-ink/60">Berat garisan</span>
                    <div class="mt-1 flex gap-2">
                        @foreach (['halus' => 'Halus', 'sederhana' => 'Sederhana', 'tebal' => 'Tebal'] as $w => $lbl)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="data.icon_style.weight" value="{{ $w }}" class="peer sr-only">
                                <span class="block rounded-xl border border-sand px-2 py-1 text-xs transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20">{{ $lbl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <span class="text-xs text-ink/60">Bekas</span>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach (['bulat-penuh' => 'Bulat penuh', 'bulat-cair' => 'Bulat cair', 'kotak-lembut' => 'Kotak lembut', 'kotak-tegas' => 'Kotak tegas', 'heksagon' => 'Heksagon', 'tanpa-bekas' => 'Tanpa bekas'] as $c => $lbl)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="data.icon_style.container" value="{{ $c }}" class="peer sr-only">
                                <span class="block rounded-xl border border-sand px-2 py-1 text-xs transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20">{{ $lbl }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Susun atur laman utama (6 pilihan) --}}
        <div>
            <label class="block text-sm font-semibold">Susun atur laman utama</label>
            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach (['hero-tengah' => 'Hero tengah', 'hero-belah' => 'Hero belah', 'grid-kad' => 'Grid kad', 'klasik-formal' => 'Klasik formal', 'hero-penuh' => 'Hero penuh (imej latar)', 'hero-mihrab' => 'Hero mihrab (lengkung)'] as $l => $lbl)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="data.layout_home" value="{{ $l }}" class="peer sr-only">
                        <div class="rounded-xl border border-sand px-2 py-2 text-center text-xs transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20">{{ $lbl }}</div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Gaya struktur (varian §7) — header / footer / kad / pembatas + animasi --}}
        <div>
            <label class="block text-sm font-semibold">Gaya struktur</label>
            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'header_style' => ['label' => 'Pengepala', 'opts' => ['padat' => 'Padat', 'gradien' => 'Gradien', 'tengah' => 'Tengah']],
                    'footer_style' => ['label' => 'Pengaki', 'opts' => ['ringkas' => 'Ringkas', 'tengah-jenama' => 'Tengah berjenama', 'tiga-lajur' => 'Tiga lajur']],
                    'card_style' => ['label' => 'Kad', 'opts' => ['lembut' => 'Lembut', 'garis' => 'Garis', 'terapung' => 'Terapung']],
                    'divider' => ['label' => 'Pembatas', 'opts' => ['tiada' => 'Tiada', 'garis-emas' => 'Garis emas', 'lengkung' => 'Lengkung']],
                ] as $field => $cfg)
                    <div>
                        <span class="text-xs text-ink/60">{{ $cfg['label'] }}</span>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            @foreach ($cfg['opts'] as $v => $lbl)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="data.{{ $field }}" value="{{ $v }}" class="peer sr-only">
                                    <span class="block rounded-lg border border-sand px-2 py-1 text-xs transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">
                <span class="text-xs text-ink/60">Animasi (masuk semasa skrol)</span>
                <div class="mt-1 flex flex-wrap gap-1.5">
                    @foreach (['tiada' => 'Tiada', 'fade' => 'Fade masuk', 'zoom' => 'Zoom masuk'] as $v => $lbl)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="data.animations" value="{{ $v }}" class="peer sr-only">
                            <span class="block rounded-lg border border-sand px-2 py-1 text-xs transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20">{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Elemen Islamik --}}
        <div>
            <label class="block text-sm font-semibold">Elemen Islamik</label>
            <div class="mt-2 space-y-1.5 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="data.islamic_elements.corak_geometri" class="h-4 w-4 accent-brand-600"> Corak geometri (latar seksyen)</label>
                <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="data.islamic_elements.pembatas_arabesque" class="h-4 w-4 accent-brand-600"> Pembatas arabesque (divider)</label>
                <p class="text-xs text-ink/55">Khat/kaligrafi khas — rekaan tambahan, akan dibincang.</p>
            </div>
        </div>

        {{-- Mood / nada --}}
        <div>
            <label class="block text-sm font-semibold">Nada penulisan <span class="text-red-600">*</span></label>
            <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-3">
                @foreach (\App\Support\Moods::options() as $m => $lbl)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="data.mood" value="{{ $m }}" class="peer sr-only">
                        <div class="rounded-xl border border-sand px-3 py-2 text-center text-sm transition peer-checked:border-brand-600 peer-checked:bg-brand-50 peer-checked:ring-1 peer-checked:ring-brand-600/20">{{ $lbl }}</div>
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-ink/55">Ini menentukan nada penulisan draf.</p>
            @error('data.mood') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Pratonton hidup (kanan) — §7.5 --}}
    <div class="lg:sticky lg:top-20 lg:self-start">
        <p class="mb-2 text-xs font-medium text-ink/50">Pratonton langsung</p>
        {{-- wire:key ikut varian animasi → morphdom ganti subtree supaya animasi main semula bila ditukar (§Fasa 14). --}}
        <div wire:key="preview-anim-{{ $data['animations'] ?? 'tiada' }}">
            <x-design-preview
                :tokens="$this->previewTokens()"
                :fonts="$this->previewFonts()"
                :icon-weight="$data['icon_style']['weight'] ?? 'sederhana'"
                :icon-container="$data['icon_style']['container'] ?? 'bulat-cair'"
                :mosque-name="$mosqueName"
                :layout="$data['layout_home'] ?? 'hero-tengah'"
                :mood="$data['mood'] ?? null"
                :card="$data['card_style'] ?? 'lembut'"
                :header="$data['header_style'] ?? 'padat'"
                :footer="$data['footer_style'] ?? 'ringkas'"
                :divider="$data['divider'] ?? 'tiada'"
                :animations="$data['animations'] ?? 'tiada'"
                :islamic="$data['islamic_elements'] ?? []"
                :logo-url="$this->previewLogoUrl()"
            />
        </div>
    </div>
</div>
