{{-- Langkah 7 — Rujukan & Inspirasi (§6 L7) --}}
@php $inp = 'mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2 text-sm focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none'; @endphp
<div class="space-y-6">
    <div>
        <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold">Laman yang anda suka (max 3)</label>
            @if (count($data['liked_refs'] ?? []) < 3)
                <button type="button" wire:click="addRow('liked_refs')" class="text-sm font-medium text-[#1B5E3F] hover:underline">+ Tambah</button>
            @endif
        </div>
        @foreach ($data['liked_refs'] ?? [] as $i => $ref)
            <div class="mt-3 rounded-lg border border-[#EFE8DC] bg-[#FAF7F2] p-3">
                <div class="flex justify-end"><button type="button" wire:click="removeRow('liked_refs', {{ $i }})" class="text-xs text-red-600">Buang</button></div>
                <input type="url" wire:model.blur="data.liked_refs.{{ $i }}.url" placeholder="https://…" class="{{ $inp }}">
                <input type="text" wire:model.blur="data.liked_refs.{{ $i }}.what_liked" maxlength="200" placeholder="Apa yang anda suka pada laman ini?" class="{{ $inp }}">
            </div>
        @endforeach
    </div>

    <div>
        <label class="block text-sm font-medium">Apa yang anda TIDAK mahu?</label>
        <textarea wire:model.blur="data.dislikes" rows="3" maxlength="500" placeholder="Cth: terlalu banyak animasi, warna gelap" class="{{ $inp }}"></textarea>
    </div>

    <div class="rounded-lg bg-[#FAF7F2] px-3 py-2 text-xs text-[#1A1A1A]/60">
        Contoh laman untuk inspirasi:
        <a href="https://mamkl.my" target="_blank" rel="noopener nofollow" class="text-[#1B5E3F] underline">mamkl.my</a> ·
        <a href="https://www.masjidwilayah.gov.my" target="_blank" rel="noopener nofollow" class="text-[#1B5E3F] underline">masjidwilayah.gov.my</a>
    </div>
</div>
