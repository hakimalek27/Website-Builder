{{--
    Renderer medan generik L4/L5 (§6 L4).
    Props: $field (definisi), $rel (path relatif dari data root), $panelData (data panel semasa).
--}}
@php
    $model = 'data.'.$rel;
    $type = $field['type'];
    $required = $field['required'] ?? false;
    $inp = 'mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2 text-sm focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none';
    // showIf — papar hanya jika medan adik-beradik sepadan.
    $visible = true;
    if (isset($field['showIf'])) {
        foreach ($field['showIf'] as $k => $v) {
            if (($panelData[$k] ?? null) != $v) { $visible = false; }
        }
    }
@endphp

@if ($visible)
<div class="mb-3">
    @if (! in_array($type, ['checkbox', 'note']))
        <label class="block text-sm font-medium">
            {{ $field['label'] }}
            @if ($required) <span class="text-red-600">*</span> @endif
            @if ($field['ai'] ?? false) <span class="ml-1 text-xs font-normal text-[#8C6D2F]" title="Boleh dijana AI — sila semak">✎ AI</span> @endif
        </label>
    @endif

    @switch($type)
        @case('text')
        @case('number')
        @case('email')
        @case('url')
        @case('tel')
            <input type="{{ $type === 'tel' ? 'tel' : ($type === 'number' ? 'number' : ($type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text'))) }}"
                   wire:model.blur="{{ $model }}" @if (isset($field['max'])) maxlength="{{ $field['max'] }}" @endif
                   placeholder="{{ $field['placeholder'] ?? '' }}" class="{{ $inp }}">
            @break

        @case('textarea')
            <textarea wire:model.blur="{{ $model }}" rows="3" @if (isset($field['max'])) maxlength="{{ $field['max'] }}" @endif
                      placeholder="{{ $field['placeholder'] ?? '' }}" class="{{ $inp }}"></textarea>
            @if (isset($field['template']))
                <button type="button" wire:click="loadTemplate('{{ $rel }}', '{{ $field['template'] }}')" class="mt-1 text-xs font-medium text-[#1B5E3F] hover:underline">Guna contoh</button>
            @endif
            @break

        @case('select')
            <select wire:model.blur="{{ $model }}" class="{{ $inp }}">
                <option value="">— Pilih —</option>
                @foreach ($field['options'] as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            @break

        @case('radio')
            <div class="mt-1 space-y-1">
                @foreach ($field['options'] as $val => $label)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="{{ $model }}" value="{{ $val }}" class="text-[#1B5E3F]"> {{ $label }}
                    </label>
                @endforeach
            </div>
            @break

        @case('checkbox')
            <label class="flex items-start gap-2 text-sm">
                <input type="checkbox" wire:model.live="{{ $model }}" class="mt-0.5 text-[#1B5E3F] focus:ring-[#1B5E3F]">
                <span>{{ $field['label'] }} @if ($field['consent'] ?? false) <span class="text-red-600">*</span> @endif</span>
            </label>
            @break

        @case('note')
            <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ $field['label'] }}</p>
            @break

        @case('zone_display')
            <p class="{{ $inp }} bg-[#FAF7F2] text-[#1A1A1A]/70">Zon solat diambil dari Langkah 1. <a href="{{ route('pic.step', ['token' => $token, 'step' => 1]) }}" class="text-[#1B5E3F] underline">Edit</a></p>
            @break

        @case('upload')
            <input type="file" wire:model="files.{{ $rel }}" class="{{ $inp }} text-xs">
            @if (! empty($panelData[$field['key']]['name'] ?? null))
                <p class="mt-1 text-xs text-[#1B5E3F]">✓ {{ $panelData[$field['key']]['name'] }}</p>
            @endif
            <div wire:loading wire:target="files.{{ $rel }}" class="mt-1 text-xs text-[#8C6D2F]">Memuat naik…</div>
            @error('files.'.$rel) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @break

        @case('upload_multi')
            <input type="file" wire:model="files.{{ $rel }}" class="{{ $inp }} text-xs">
            @php $uploaded = $panelData[$field['key']] ?? []; @endphp
            @if (! empty($uploaded))
                <p class="mt-1 text-xs text-[#1B5E3F]">✓ {{ count($uploaded) }} fail dimuat naik</p>
            @endif
            <div wire:loading wire:target="files.{{ $rel }}" class="mt-1 text-xs text-[#8C6D2F]">Memuat naik…</div>
            @error('files.'.$rel) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @break

        @case('facility_checklist')
            <div class="mt-1 grid grid-cols-2 gap-1.5 sm:grid-cols-3">
                @foreach (\App\Support\PageCatalog::facilities() as $fk => $flabel)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.live="{{ $model }}" value="{{ $fk }}" class="text-[#1B5E3F]"> {{ $flabel }}
                    </label>
                @endforeach
            </div>
            @break

        @case('repeater_text')
            <div class="space-y-2">
                @foreach ($panelData[$field['key']] ?? [] as $i => $row)
                    <div class="flex gap-2">
                        <input type="text" wire:model.blur="data.{{ $rel }}.{{ $i }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="flex-1 rounded-lg border border-[#EFE8DC] px-3 py-2 text-sm">
                        <button type="button" wire:click="removeRow('{{ $rel }}', {{ $i }})" class="text-sm text-red-600">×</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addRow('{{ $rel }}')" class="text-sm font-medium text-[#1B5E3F] hover:underline">+ Tambah</button>
            </div>
            @break

        @case('repeater')
            <div class="space-y-3">
                @foreach ($panelData[$field['key']] ?? [] as $i => $row)
                    <div class="rounded-lg border border-[#EFE8DC] bg-[#FAF7F2] p-3">
                        <div class="flex justify-end">
                            <button type="button" wire:click="removeRow('{{ $rel }}', {{ $i }})" class="text-xs text-red-600 hover:underline">Buang baris</button>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($field['item'] as $sub)
                                @include('livewire.wizard.panels._field', ['field' => $sub, 'rel' => $rel.'.'.$i.'.'.$sub['key'], 'panelData' => $row])
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @if (! isset($field['max']) || count($panelData[$field['key']] ?? []) < $field['max'])
                    <button type="button" wire:click="addRow('{{ $rel }}')" class="text-sm font-medium text-[#1B5E3F] hover:underline">+ Tambah baris</button>
                @endif
                @if (($field['template'] ?? null) === 'faq')
                    <button type="button" wire:click="loadFaqCommon('{{ $rel }}')" class="ml-3 text-sm font-medium text-[#8C6D2F] hover:underline">Muatkan 8 soalan lazim</button>
                @endif
            </div>
            @break
    @endswitch

    @if ($field['pdpa'] ?? false)
        <p class="mt-2 rounded bg-blue-50 px-3 py-2 text-xs text-blue-800">Pastikan setiap individu bersetuju nama &amp; gambar dipaparkan (PDPA).</p>
    @endif

    @error($model) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
@endif
