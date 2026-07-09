<div @if ($this->activeGeneration) wire:poll.3s @endif>
    {{-- Kad kuota + jana --}}
    <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-sand">
        <div class="grid gap-6 p-6 sm:grid-cols-[1fr_auto] sm:items-center sm:p-8">
            <div>
                <h2 class="font-display text-2xl font-bold text-brand-800">Jana Draf Laman</h2>
                <p class="mt-1 text-sm text-ink/60">Sistem menjana draf sampel laman anda menggunakan AI.</p>

                <div class="mt-5 grid max-w-md gap-3 sm:grid-cols-2">
                    @php
                        $aiUsed = $this->project->quota_ai_used;
                        $aiTotal = max(1, $this->project->quota_ai_total);
                        $dUsed = $this->project->quota_design_used;
                    @endphp
                    <div class="rounded-2xl bg-cream p-4 ring-1 ring-sand">
                        <div class="flex items-center justify-between text-xs font-medium text-ink/55">
                            <span class="flex items-center gap-1.5">{!! \App\Support\Lucide::svg('Sparkles', 1.75, 'h-3.5 w-3.5 text-brand-600') !!} Jana AI</span>
                            <span>{{ $aiUsed }}/{{ $this->project->quota_ai_total }}</span>
                        </div>
                        <x-ui.progress :value="(int) round($aiUsed / $aiTotal * 100)" class="mt-2 h-1.5" />
                    </div>
                    <div class="rounded-2xl bg-cream p-4 ring-1 ring-sand">
                        <div class="flex items-center justify-between text-xs font-medium text-ink/55">
                            <span class="flex items-center gap-1.5">{!! \App\Support\Lucide::svg('Sparkles', 1.75, 'h-3.5 w-3.5 text-gold-500') !!} Render reka</span>
                            <span>{{ $dUsed }}/5</span>
                        </div>
                        <x-ui.progress :value="(int) round($dUsed / 5 * 100)" tone="gold" class="mt-2 h-1.5" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-stretch gap-2 sm:items-end">
                @error('gate')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="button" wire:click="generate" @disabled($this->disabledReason !== null)
                    class="btn btn-gold btn-lg disabled:opacity-40">
                    {!! \App\Support\Lucide::svg('Sparkles', 2, 'h-5 w-5') !!}
                    Jana Draf
                </button>
                @if ($this->disabledReason)
                    <p class="max-w-[16rem] text-xs text-gold-700 sm:text-right">{{ $this->disabledReason }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Progres semasa penjanaan --}}
    @if ($this->activeGeneration)
        @php $step = $this->activeGeneration->progress_step; @endphp
        <div class="mt-6 overflow-hidden rounded-3xl bg-white p-6 shadow-soft ring-1 ring-sand sm:p-8">
            <div class="flex items-center gap-3">
                <span class="relative grid h-10 w-10 place-items-center rounded-full bg-gold-400/15 text-gold-600">
                    <span class="absolute inset-0 animate-ping rounded-full bg-gold-400/20"></span>
                    {!! \App\Support\Lucide::svg('Loader', 2, 'h-5 w-5') !!}
                </span>
                <h3 class="font-display text-xl font-bold text-brand-800">Sedang menjana draf…</h3>
            </div>
            <ol class="mt-6 space-y-1">
                @foreach ($progressSteps as $i => $label)
                    @php $n = $i + 1; @endphp
                    <li class="relative flex items-center gap-4 pb-5 last:pb-0">
                        @unless ($loop->last)
                            <span @class(['absolute top-8 left-[15px] h-full w-px', 'bg-brand-600' => $step > $n, 'bg-sand' => $step <= $n])></span>
                        @endunless
                        <span @class([
                            'relative z-10 grid h-8 w-8 shrink-0 place-items-center rounded-full text-xs font-semibold',
                            'bg-brand-600 text-white' => $step > $n,
                            'bg-gold-400 text-brand-900 animate-pulse' => $step === $n,
                            'bg-sand text-ink/40' => $step < $n,
                        ])>
                            @if ($step > $n)
                                {!! \App\Support\Lucide::svg('Check', 2.5, 'h-4 w-4') !!}
                            @else
                                {{ $n }}
                            @endif
                        </span>
                        <span class="text-sm {{ $step >= $n ? 'font-medium text-ink' : 'text-ink/40' }}">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
            <p class="mt-4 flex items-center gap-2 rounded-xl bg-cream px-4 py-3 text-xs text-ink/60">
                {!! \App\Support\Lucide::svg('Info', 2, 'h-4 w-4 shrink-0 text-brand-600') !!}
                Anda boleh tutup halaman ini — kami akan WhatsApp bila siap.
            </p>
        </div>
    @endif

    {{-- Senarai draf terdahulu --}}
    @if ($this->generations->isNotEmpty())
        <div class="mt-8">
            <h3 class="mb-3 px-1 text-xs font-semibold tracking-wider text-ink/45 uppercase">Draf terdahulu</h3>
            <div class="space-y-2">
                @foreach ($this->generations as $gen)
                    <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3.5 shadow-xs ring-1 ring-sand">
                        <span class="flex items-center gap-3 text-sm">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-50 text-brand-600">
                                {!! \App\Support\Lucide::svg('FileText', 1.75, 'h-4 w-4') !!}
                            </span>
                            <span>
                                <span class="block font-medium text-ink">{{ ucfirst($gen->type->value) }}</span>
                                <span class="block text-xs text-ink/45">{{ $gen->created_at->format('d/m/Y H:i') }}</span>
                            </span>
                        </span>
                        <span class="flex items-center gap-3">
                            <span @class([
                                'chip',
                                'bg-brand-50 text-brand-700' => $gen->status === App\Enums\GenerationStatus::Succeeded,
                                'bg-red-100 text-red-700' => $gen->status === App\Enums\GenerationStatus::Failed,
                                'bg-gold-400/15 text-gold-700' => $gen->status->isActive(),
                            ])>{{ $gen->status->value }}</span>
                            @if ($gen->status === App\Enums\GenerationStatus::Succeeded)
                                <span class="hidden text-xs font-medium text-brand-600 sm:inline">Draf sedia</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
