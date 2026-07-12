{{-- Langkah 2 — Pilih Templat Rujukan (§Fasa 16 mod 'template'). --}}
<div class="space-y-6">
    {{-- Intro --}}
    <div class="rounded-xl border border-sand bg-cream/60 p-4">
        <p class="text-sm text-ink/70">Pilih <strong>satu templat rujukan</strong> yang paling hampir dengan gaya laman impian anda — atau tampal pautan laman lain yang anda minati. Pasukan REKA akan membina laman anda berdasarkan pilihan ini <strong>+ nota anda</strong> (bukan salinan 1:1 — kami olah agar sesuai dan unik).</p>
    </div>

    {{-- Carian --}}
    <div>
        <input type="search" wire:model.live.debounce.300ms="templateSearch"
               placeholder="Cari templat (nama atau gaya)…"
               class="w-full rounded-xl border border-sand px-3 py-2 text-sm">
    </div>

    {{-- Pilihan semasa --}}
    @php $chosen = $data['template_snapshot'] ?? null; @endphp
    @if ($chosen)
        <div class="flex items-center justify-between gap-3 rounded-xl border-2 border-brand-600 bg-brand-50 p-3">
            <div class="min-w-0">
                <span class="text-xs font-medium text-brand-700">Templat dipilih</span>
                <p class="truncate font-semibold text-brand-800">{{ $chosen['name'] }}</p>
                <a href="{{ $chosen['url'] }}" target="_blank" rel="noopener noreferrer" class="text-xs text-brand-700 underline">Lihat ↗</a>
            </div>
            <button type="button" wire:click="clearTemplate" class="shrink-0 rounded-lg border border-sand bg-white px-3 py-1.5 text-xs hover:border-brand-600/40">Buang pilihan</button>
        </div>
    @endif

    {{-- Galeri --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($templates as $t)
            <div wire:key="tpl-{{ $t->id }}" @class([
                'flex flex-col overflow-hidden rounded-xl border-2 bg-white transition',
                'border-brand-600 ring-1 ring-brand-600/20' => ($data['template_id'] ?? null) === $t->id,
                'border-sand hover:border-brand-600/40' => ($data['template_id'] ?? null) !== $t->id,
            ])>
                @if ($t->thumbnail_path)
                    {{-- URL relatif (same-origin) — kukuh pada mana-mana host/port + CSP img-src 'self'. --}}
                    <img src="/storage/{{ $t->thumbnail_path }}"
                         alt="{{ $t->name }}" loading="lazy" class="aspect-video w-full object-cover">
                @else
                    <div class="flex aspect-video w-full items-center justify-center bg-brand-50 text-4xl font-semibold text-brand-300" style="font-family: serif;">{{ \Illuminate\Support\Str::substr($t->name, 0, 1) }}</div>
                @endif
                <div class="flex flex-1 flex-col p-3">
                    <p class="text-sm font-semibold text-brand-800">{{ $t->name }}</p>
                    @if (! empty($t->style_tags))
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach (array_slice($t->style_tags, 0, 3) as $tag)
                                <span class="rounded-full bg-cream px-2 py-0.5 text-[10px] text-ink/60">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click="showTemplateDetail('{{ $t->id }}')" class="flex-1 rounded-lg border border-sand px-2 py-1.5 text-xs hover:border-brand-600/40">Butiran</button>
                        <button type="button" wire:click="selectTemplate('{{ $t->id }}')" class="flex-1 rounded-lg bg-brand-600 px-2 py-1.5 text-xs font-medium text-white hover:bg-brand-700">Pilih</button>
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-xl border border-sand bg-cream/40 p-4 text-sm text-ink/60">Tiada templat sepadan. Cuba kata kunci lain, atau tampal pautan laman anda sendiri di bawah.</p>
        @endforelse
    </div>

    {{-- Panel butiran --}}
    @if ($templateDetailId)
        @php $detail = $templates->firstWhere('id', $templateDetailId); @endphp
        @if ($detail)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="showTemplateDetail(null)">
                <div class="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-5 shadow-xl">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-semibold text-brand-800">{{ $detail->name }}</h3>
                        <button type="button" wire:click="showTemplateDetail(null)" class="text-xl leading-none text-ink/40 hover:text-ink">&times;</button>
                    </div>
                    @if ($detail->description)
                        <p class="mt-2 text-sm text-ink/70">{{ $detail->description }}</p>
                    @endif
                    @if (! empty($detail->screenshots))
                        <div class="mt-3 space-y-2">
                            @foreach ($detail->screenshots as $shot)
                                <img src="/storage/{{ $shot }}" alt="" loading="lazy" class="w-full rounded-lg border border-sand">
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ $detail->demo_url ?: $detail->url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-sand px-3 py-2 text-sm hover:border-brand-600/40">Lihat demo penuh ↗</a>
                        <button type="button" wire:click="selectTemplate('{{ $detail->id }}')" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">Pilih Templat Ini</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ATAU link sendiri --}}
    <div class="flex items-center gap-3 text-xs text-ink/40">
        <span class="h-px flex-1 bg-sand"></span> ATAU <span class="h-px flex-1 bg-sand"></span>
    </div>
    <div>
        <label class="block text-sm font-semibold">Tampal pautan laman lain yang anda suka</label>
        <input type="url" wire:model.blur="data.template_custom_url" placeholder="https://…"
               class="mt-2 w-full rounded-xl border border-sand px-3 py-2 text-sm">
        <p class="mt-1 text-xs text-ink/55">Nampak laman masjid / NGO lain yang anda minati? Kongsi pautannya di sini.</p>
        @error('data.template_custom_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Nota berstruktur --}}
    <div class="space-y-3 rounded-xl border border-sand bg-white p-4">
        <p class="text-sm font-semibold">Nota reka bentuk anda</p>
        <div>
            <label class="text-xs font-medium text-ink/70">Apa yang anda <strong>SUKA</strong> pada templat / laman ini?</label>
            <textarea wire:model.blur="data.template_notes.suka" rows="2" maxlength="1000"
                      class="mt-1 w-full rounded-xl border border-sand px-3 py-2 text-sm"
                      placeholder="cth: warna hijau tenang, susun atur kemas, banyak ruang putih…"></textarea>
        </div>
        <div>
            <label class="text-xs font-medium text-ink/70">Apa yang perlu <strong>DIUBAH / DIBUANG</strong>?</label>
            <textarea wire:model.blur="data.template_notes.ubah" rows="2" maxlength="1000"
                      class="mt-1 w-full rounded-xl border border-sand px-3 py-2 text-sm"
                      placeholder="cth: jangan guna slider besar, buang bahagian blog…"></textarea>
        </div>
        <div>
            <label class="text-xs font-medium text-ink/70">Apa yang perlu <strong>DITAMBAH</strong>?</label>
            <textarea wire:model.blur="data.template_notes.tambah" rows="2" maxlength="1000"
                      class="mt-1 w-full rounded-xl border border-sand px-3 py-2 text-sm"
                      placeholder="cth: bahagian waktu solat besar di atas, butang derma menonjol…"></textarea>
        </div>
    </div>

    {{-- Mood / nada (kekal wajib) --}}
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
        <p class="mt-1 text-xs text-ink/55">Ini menentukan nada penulisan kandungan laman.</p>
        @error('data.mood') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
