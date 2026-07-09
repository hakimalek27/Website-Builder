{{-- Langkah 4 — Kandungan Halaman (enjin sub-borang, §6 L4) --}}
@php
    use App\Support\PageCatalog;
    $meta = PageCatalog::meta();
    $panels = PageCatalog::panels();
@endphp
<div class="space-y-3">
    <p class="text-sm text-[#1A1A1A]/70">Isi butiran bagi setiap halaman yang anda pilih. Panel muncul mengikut pilihan Langkah 3.</p>

    @forelse ($activePanels as $pageKey)
        @php
            $panelData = $data['panels'][$pageKey] ?? [];
            $filled = collect($panelData)->filter(fn ($v) => filled($v))->isNotEmpty();
        @endphp
        <details class="group rounded-xl border border-[#EFE8DC] bg-white" wire:key="panel-{{ $pageKey }}">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 select-none">
                <span class="font-medium text-[#0F3D27]">{{ $meta[$pageKey]['label'] }}</span>
                <span class="flex items-center gap-2">
                    @if ($filled)
                        <span class="rounded-full bg-[#1B5E3F]/10 px-2 py-0.5 text-xs font-medium text-[#0F3D27]">✓ Terisi</span>
                    @else
                        <span class="rounded-full bg-[#EFE8DC] px-2 py-0.5 text-xs text-[#1A1A1A]/50">Kosong</span>
                    @endif
                    <span class="text-[#1A1A1A]/40 transition group-open:rotate-180">▾</span>
                </span>
            </summary>
            <div class="border-t border-[#EFE8DC] p-4">
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
        <div class="rounded-xl border border-dashed border-[#EFE8DC] p-8 text-center text-sm text-[#1A1A1A]/50">
            Tiada panel untuk diisi. Sila pilih halaman di <a href="{{ route('pic.step', ['token' => $token, 'step' => 3]) }}" class="text-[#1B5E3F] underline">Langkah 3</a> dahulu.
        </div>
    @endforelse
</div>
