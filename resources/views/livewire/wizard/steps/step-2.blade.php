{{-- Langkah 2 (§6 L2) — dispatcher §Fasa 16: galeri templat (mod 'template') atau reka bentuk klasik. --}}
@if ($templateMode)
    @include('livewire.wizard.steps.step-2-template')
@else
    @include('livewire.wizard.steps.step-2-design')
@endif
