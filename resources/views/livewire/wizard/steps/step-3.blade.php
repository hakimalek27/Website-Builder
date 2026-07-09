{{-- Langkah 3 — Struktur Halaman (§6 L3) --}}
@php
    use App\Support\PageCatalog;
    $meta = PageCatalog::meta();
    $selected = $data['pages'] ?? [];
    $enabledCount = count(array_unique(array_merge($selected, PageCatalog::MANDATORY)))
        + collect($data['custom'] ?? [])->filter(fn ($c) => filled($c['name'] ?? null))->count();
@endphp
<div>
    <div class="mb-4 flex items-center justify-between rounded-xl bg-[#1B5E3F]/5 px-4 py-3">
        <p class="text-sm text-[#1A1A1A]/70">Tanda halaman yang anda mahu pada laman. Halaman utama &amp; hubungi kekal wajib.</p>
        <span class="shrink-0 rounded-full bg-[#1B5E3F] px-3 py-1 text-sm font-semibold text-white">Anggaran: {{ $enabledCount }} halaman</span>
    </div>

    {{-- Halaman utama (wajib) --}}
    <label class="mb-4 flex items-center gap-3 rounded-lg border border-[#1B5E3F]/30 bg-white px-3 py-2.5">
        <input type="checkbox" checked disabled class="text-[#1B5E3F]">
        <span class="text-sm font-medium">Halaman Utama <span class="text-xs text-[#8C6D2F]">(wajib)</span></span>
    </label>

    @foreach (PageCatalog::clusters() as $cluster => $keys)
        <div class="mb-4">
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#1A1A1A]/50">{{ $cluster }}</h3>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($keys as $key)
                    @php $isMandatory = in_array($key, PageCatalog::MANDATORY, true); @endphp
                    <label class="flex items-start gap-2.5 rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 cursor-pointer hover:border-[#1B5E3F]/40">
                        <input type="checkbox"
                               @if ($isMandatory) checked disabled @else wire:model.live="data.pages" value="{{ $key }}" @endif
                               class="mt-0.5 text-[#1B5E3F] focus:ring-[#1B5E3F]">
                        <span class="flex-1">
                            <span class="block text-sm font-medium">
                                {{ $meta[$key]['label'] }}
                                @if ($isMandatory) <span class="text-xs text-[#8C6D2F]">(wajib)</span> @endif
                            </span>
                            <span class="block text-xs text-[#1A1A1A]/50" title="{{ $meta[$key]['tooltip'] }}">{{ $meta[$key]['tooltip'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Halaman custom (max 3) --}}
    <div class="mt-6 rounded-xl border border-[#EFE8DC] bg-[#FAF7F2] p-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold">Halaman tambahan (custom) — max 3</h3>
            @if (count($data['custom'] ?? []) < 3)
                <button type="button" wire:click="addRow('custom')" class="text-sm font-medium text-[#1B5E3F] hover:underline">+ Tambah</button>
            @endif
        </div>
        @foreach ($data['custom'] ?? [] as $i => $custom)
            <div class="mt-3 grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                <input type="text" wire:model.blur="data.custom.{{ $i }}.name" placeholder="Nama halaman" class="rounded-lg border border-[#EFE8DC] px-3 py-2 text-sm">
                <input type="text" wire:model.blur="data.custom.{{ $i }}.purpose" placeholder="Tujuan" class="rounded-lg border border-[#EFE8DC] px-3 py-2 text-sm">
                <button type="button" wire:click="removeRow('custom', {{ $i }})" class="text-sm text-red-600 hover:underline">Buang</button>
            </div>
        @endforeach
    </div>
</div>
