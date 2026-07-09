{{-- Langkah 7 — Rujukan & Inspirasi (§6 L7) --}}
@php $inp = 'mt-1 w-full rounded-xl border border-sand bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/12 focus:outline-none'; @endphp
<div class="space-y-6">
    <div>
        <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold">Laman yang anda suka (max 3)</label>
            @if (count($data['liked_refs'] ?? []) < 3)
                <button type="button" wire:click="addRow('liked_refs')" class="text-sm font-medium text-brand-600 hover:underline">+ Tambah</button>
            @endif
        </div>
        @foreach ($data['liked_refs'] ?? [] as $i => $ref)
            <div class="mt-3 rounded-xl border border-sand bg-cream p-3">
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

    <div class="rounded-xl bg-cream px-3 py-2 text-xs text-ink/60">
        Contoh laman untuk inspirasi:
        <a href="https://mamkl.my" target="_blank" rel="noopener nofollow" class="text-brand-600 underline">mamkl.my</a> ·
        <a href="https://www.masjidwilayah.gov.my" target="_blank" rel="noopener nofollow" class="text-brand-600 underline">masjidwilayah.gov.my</a>
    </div>
</div>
