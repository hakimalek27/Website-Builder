<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        {{-- Inline-style container: elak kelas Tailwind arbitrari yang tidak dikompil tema panel. --}}
        <div style="display:flex; gap:.75rem; margin-top:1.5rem; flex-wrap:wrap;">
            <x-filament::button type="submit">
                Simpan Tetapan
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="testWhatsapp" wire:loading.attr="disabled">
                Uji Hantar WhatsApp
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
