<div>
    {{-- Penunjuk langkah --}}
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('pic.home', ['token' => $token]) }}" class="text-sm text-[#1B5E3F] hover:underline">&larr; Kembali ke senarai</a>
        <span class="text-xs font-medium text-[#1A1A1A]/50">Langkah {{ $step }} / {{ $totalSteps - 1 }}</span>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#EFE8DC]">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-[#0F3D27]">{{ $stepMeta['title'] }}</h1>
                <p class="mt-1 text-sm text-[#1A1A1A]/60">{{ $stepMeta['subtitle'] }}</p>
            </div>
            @if ($savedAt)
                <span class="shrink-0 rounded-full bg-[#1B5E3F]/10 px-3 py-1 text-xs font-medium text-[#0F3D27]">
                    Disimpan &checkmark; {{ $savedAt }}
                </span>
            @endif
        </div>

        @if ($readOnly)
            <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
                Draf telah diluluskan — borang ini kini baca-sahaja. Hubungi kami untuk sebarang perubahan.
            </div>
        @endif

        <div @class(['mt-6 space-y-6', 'pointer-events-none opacity-60' => $readOnly])>
            @includeIf('livewire.wizard.steps.step-'.$step)
        </div>
    </div>

    {{-- Navigasi --}}
    <div class="mt-6 flex items-center justify-between gap-3">
        <button type="button" wire:click="back" @disabled($step === 0)
                class="rounded-xl border border-[#1B5E3F]/30 px-5 py-2.5 text-sm font-semibold text-[#0F3D27] hover:bg-[#EFE8DC] disabled:opacity-40 transition">
            Kembali
        </button>
        <button type="button" wire:click="saveAndExit"
                class="rounded-xl px-5 py-2.5 text-sm font-medium text-[#1A1A1A]/70 hover:text-[#0F3D27] transition">
            Simpan &amp; Keluar
        </button>
        <button type="button" wire:click="next"
                class="ml-auto rounded-xl bg-[#1B5E3F] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0F3D27] transition">
            Seterusnya
        </button>
    </div>
</div>
