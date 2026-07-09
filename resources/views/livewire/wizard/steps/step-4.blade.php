{{-- Langkah 4 — Kandungan Halaman (enjin sub-borang, §6 L4) --}}
@php
    use App\Support\PageCatalog;
    $meta = PageCatalog::meta();
    $panels = PageCatalog::panels();
@endphp
<div class="space-y-3">
    <p class="text-sm text-ink/70">Isi butiran bagi setiap halaman yang anda pilih. Panel muncul mengikut pilihan Langkah 3.</p>

    @forelse ($activePanels as $pageKey)
        @php
            $panelData = $data['panels'][$pageKey] ?? [];
            $filled = collect($panelData)->filter(fn ($v) => filled($v))->isNotEmpty();
        @endphp
        <details class="group rounded-xl border border-sand bg-white" wire:key="panel-{{ $pageKey }}">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 select-none">
                <span class="font-medium text-brand-800">{{ $meta[$pageKey]['label'] }}</span>
                <span class="flex items-center gap-2">
                    @if ($filled)
                        <span class="rounded-full bg-brand-600/10 px-2 py-0.5 text-xs font-medium text-brand-800">✓ Terisi</span>
                    @else
                        <span class="rounded-full bg-sand px-2 py-0.5 text-xs text-ink/50">Kosong</span>
                    @endif
                    <span class="text-ink/40 transition group-open:rotate-180">▾</span>
                </span>
            </summary>
            <div class="border-t border-sand p-4">
                @foreach ($panels[$pageKey] as $field)
                    @include('livewire.wizard.panels._field', [
                        'field' => $field,
                        'rel' => 'panels.'.$pageKey.'.'.$field['key'],
                        'panelData' => $panelData,
                    ])
                @endforeach
            </div>
        </details>
    @empty
        <div class="rounded-xl border border-dashed border-sand p-8 text-center text-sm text-ink/50">
            Tiada panel untuk diisi. Sila pilih halaman di <a href="{{ route('pic.step', ['token' => $token, 'step' => 3]) }}" class="text-brand-600 underline">Langkah 3</a> dahulu.
        </div>
    @endforelse
</div>
