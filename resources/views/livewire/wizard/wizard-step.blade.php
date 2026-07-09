<div>
    {{-- Penunjuk langkah + progres --}}
    <div class="mb-5">
        <div class="mb-2 flex items-center justify-between">
            <a href="{{ route('pic.home', ['token' => $token]) }}" class="flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                {!! \App\Support\Lucide::svg('ArrowLeft', 2, 'h-4 w-4') !!} Senarai
            </a>
            <span class="text-xs font-semibold tracking-wide text-ink/50 uppercase">
                Langkah {{ $step }} <span class="text-ink/30">/ {{ $totalSteps - 1 }}</span>
            </span>
        </div>
        <x-ui.progress :value="(int) round($step / max(1, $totalSteps - 1) * 100)" class="h-1.5" />
    </div>

    <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-sand sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-brand-800">{{ $stepMeta['title'] }}</h1>
                <p class="mt-1 text-sm text-ink/60">{{ $stepMeta['subtitle'] }}</p>
            </div>
            @if ($savedAt)
                <span class="animate-scale-in flex shrink-0 items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">
                    {!! \App\Support\Lucide::svg('Check', 2.5, 'h-3.5 w-3.5') !!} Disimpan {{ $savedAt }}
                </span>
            @endif
        </div>

        @if ($readOnly)
            <div class="mt-4 flex items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
                {!! \App\Support\Lucide::svg('Lock', 2, 'h-4 w-4 shrink-0 text-amber-500') !!}
                Draf telah diluluskan — borang ini kini baca-sahaja. Hubungi kami untuk sebarang perubahan.
            </div>
        @endif

        <div @class(['mt-6 space-y-6', 'pointer-events-none opacity-60' => $readOnly])>
            @includeIf('livewire.wizard.steps.step-' . $step)
        </div>
    </div>

    {{-- Navigasi melekit --}}
    <div class="sticky bottom-4 z-10 mt-6">
        <div class="flex items-center gap-2 rounded-2xl border border-sand bg-white/85 p-2.5 shadow-lift backdrop-blur-md">
            <button type="button" wire:click="back" @disabled($step === 0)
                class="btn btn-outline disabled:opacity-40">
                {!! \App\Support\Lucide::svg('ArrowLeft', 2, 'h-4 w-4') !!} Kembali
            </button>
            <button type="button" wire:click="saveAndExit" class="btn btn-ghost hidden sm:inline-flex">
                Simpan &amp; Keluar
            </button>
            <button type="button" wire:click="next" class="btn btn-primary ml-auto">
                Seterusnya {!! \App\Support\Lucide::svg('ArrowRight', 2, 'h-4 w-4') !!}
            </button>
        </div>
    </div>
</div>
