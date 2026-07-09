<div @if ($this->activeGeneration) wire:poll.3s @endif>
    {{-- Kad kuota --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-[#EFE8DC]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-[#0F3D27]">Jana Draf Laman</h2>
                <p class="mt-1 text-sm text-[#1A1A1A]/60">
                    Jana AI: {{ $this->project->quota_ai_used }}/{{ $this->project->quota_ai_total }} digunakan ·
                    Render reka bentuk: {{ $this->project->quota_design_used }}/5
                </p>
            </div>
            <div>
                @error('gate') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror
                <button type="button" wire:click="generate"
                        @disabled($this->disabledReason !== null)
                        class="rounded-xl bg-[#1B5E3F] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0F3D27] disabled:opacity-40 disabled:cursor-not-allowed transition">
                    Jana Draf
                </button>
                @if ($this->disabledReason)
                    <p class="mt-1 text-xs text-[#8C6D2F]">{{ $this->disabledReason }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Progres semasa penjanaan --}}
    @if ($this->activeGeneration)
        @php $step = $this->activeGeneration->progress_step; @endphp
        <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-[#EFE8DC]">
            <h3 class="font-semibold text-[#0F3D27]">Sedang menjana draf…</h3>
            <ol class="mt-4 space-y-3">
                @foreach ($progressSteps as $i => $label)
                    @php $n = $i + 1; @endphp
                    <li class="flex items-center gap-3 text-sm">
                        <span @class([
                            'flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold',
                            'bg-[#1B5E3F] text-white' => $step > $n,
                            'bg-[#C9A961] text-white animate-pulse' => $step === $n,
                            'bg-[#EFE8DC] text-[#1A1A1A]/40' => $step < $n,
                        ])>{{ $step > $n ? '✓' : $n }}</span>
                        <span class="{{ $step >= $n ? 'text-[#1A1A1A]' : 'text-[#1A1A1A]/40' }}">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
            <p class="mt-4 rounded-lg bg-[#FAF7F2] px-3 py-2 text-xs text-[#1A1A1A]/60">
                Anda boleh tutup halaman ini — kami akan WhatsApp bila siap.
            </p>
        </div>
    @endif

    {{-- Senarai draf terdahulu --}}
    @if ($this->generations->isNotEmpty())
        <div class="mt-6">
            <h3 class="mb-2 text-sm font-semibold text-[#0F3D27]">Draf terdahulu</h3>
            <div class="space-y-2">
                @foreach ($this->generations as $gen)
                    <div class="flex items-center justify-between rounded-xl bg-white px-4 py-3 ring-1 ring-[#EFE8DC]">
                        <span class="text-sm">
                            <span class="font-medium">{{ ucfirst($gen->type->value) }}</span>
                            <span class="ml-2 text-xs text-[#1A1A1A]/50">{{ $gen->created_at->format('d/m/Y H:i') }}</span>
                        </span>
                        <span class="flex items-center gap-3">
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-[#1B5E3F]/10 text-[#0F3D27]' => $gen->status === App\Enums\GenerationStatus::Succeeded,
                                'bg-red-100 text-red-700' => $gen->status === App\Enums\GenerationStatus::Failed,
                                'bg-[#C9A961]/20 text-[#8C6D2F]' => $gen->status->isActive(),
                            ])>{{ $gen->status->value }}</span>
                            @if ($gen->status === App\Enums\GenerationStatus::Succeeded)
                                <span class="text-xs font-medium text-[#1B5E3F]">Draf sedia</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
