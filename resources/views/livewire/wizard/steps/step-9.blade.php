{{-- Langkah 9 — Nota, Perakuan & Persetujuan (§6 L9) --}}
@php $inp = 'mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2 text-sm focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none'; @endphp
<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium">Nota bebas (pilihan)</label>
        <textarea wire:model.blur="data.free_notes" rows="3" maxlength="2000" placeholder="Apa-apa lagi yang anda mahu kami tahu — gaya, ciri, harapan" class="{{ $inp }}"></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium">Anggaran bajet (membantu kami mencadang pakej)</label>
        <select wire:model.blur="data.budget_hint" class="{{ $inp }}">
            <option value="">— Pilih —</option>
            <option value="<RM1k">Kurang RM1,000</option>
            <option value="RM1-3k">RM1,000 – RM3,000</option>
            <option value="RM3-5k">RM3,000 – RM5,000</option>
            <option value=">RM5k">Lebih RM5,000</option>
            <option value="bincang">Bincang</option>
        </select>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div>
            <label class="block text-sm font-medium">Nama anda <span class="text-red-600">*</span></label>
            <input type="text" wire:model.blur="data.pic_name" class="{{ $inp }}">
            @error('data.pic_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Jawatan <span class="text-red-600">*</span></label>
            <input type="text" wire:model.blur="data.pic_position" placeholder="Cth: Setiausaha AJK" class="{{ $inp }}">
            @error('data.pic_position') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Telefon <span class="text-red-600">*</span></label>
            <input type="tel" wire:model.blur="data.pic_phone" class="{{ $inp }}">
            @error('data.pic_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="space-y-3 rounded-xl border border-[#EFE8DC] bg-[#FAF7F2] p-4">
        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" wire:model.live="data.consent_pdpa" class="mt-0.5 text-[#1B5E3F] focus:ring-[#1B5E3F]">
            <span>
                {{ __('reka.consent_pdpa', ['business' => config('reka.business_name')]) }}
                <a href="{{ route('privasi') }}" target="_blank" class="text-[#1B5E3F] underline">Notis Privasi</a>.
            </span>
        </label>
        @error('data.consent_pdpa') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

        <label class="flex items-start gap-2.5 text-sm">
            <input type="checkbox" wire:model.live="data.declare_truth_authority" class="mt-0.5 text-[#1B5E3F] focus:ring-[#1B5E3F]">
            <span>{{ __('reka.declare_authority') }}</span>
        </label>
        @error('data.declare_truth_authority') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <p class="text-center text-sm text-[#1A1A1A]/60">
        Setelah semua langkah lengkap, pergi ke halaman <a href="{{ route('pic.semak', ['token' => $token]) }}" class="text-[#1B5E3F] underline">Semak &amp; Hantar</a>.
    </p>
</div>
