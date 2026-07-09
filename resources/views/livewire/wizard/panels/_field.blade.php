{{--
    Renderer medan generik L4/L5 (§6 L4).
    Props: $field (definisi), $rel (path relatif dari data root), $panelData (data panel semasa).
--}}
@php
    $model = 'data.' . $rel;
    $type = $field['type'];
    $required = $field['required'] ?? false;
    // showIf — papar hanya jika medan adik-beradik sepadan.
    $visible = true;
    if (isset($field['showIf'])) {
        foreach ($field['showIf'] as $k => $v) {
            if (($panelData[$k] ?? null) != $v) {
                $visible = false;
            }
        }
    }
@endphp

@if ($visible)
    <div class="mb-3" wire:key="f-{{ $rel }}">
        @if (! in_array($type, ['checkbox', 'note']))
            <label class="label flex items-center gap-1.5">
                {{ $field['label'] }}
                @if ($required)
                    <span class="text-red-600">*</span>
                @endif
                @if ($field['ai'] ?? false)
                    <span class="badge bg-gold-400/15 text-gold-700" title="Boleh dijana AI — sila semak">✎ AI</span>
                @endif
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
                    placeholder="{{ $field['placeholder'] ?? '' }}" class="input">
            @break

            @case('textarea')
                <textarea wire:model.blur="{{ $model }}" rows="3" @if (isset($field['max'])) maxlength="{{ $field['max'] }}" @endif
                    placeholder="{{ $field['placeholder'] ?? '' }}" class="input"></textarea>
                @if (isset($field['template']))
                    <button type="button" wire:click="loadTemplate('{{ $rel }}', '{{ $field['template'] }}')"
                        class="mt-1.5 text-xs font-medium text-brand-600 hover:underline">Guna contoh</button>
                @endif
            @break

            @case('select')
                <select wire:model.blur="{{ $model }}" class="input">
                    <option value="">— Pilih —</option>
                    @foreach ($field['options'] as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            @break

            @case('radio')
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($field['options'] as $val => $label)
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-sand px-3.5 py-2.5 text-sm transition has-[:checked]:border-brand-600/40 has-[:checked]:bg-brand-50">
                            <input type="radio" wire:model.live="{{ $model }}" value="{{ $val }}" class="h-4 w-4 accent-brand-600"> {{ $label }}
                        </label>
                    @endforeach
                </div>
            @break

            @case('checkbox')
                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-sand px-3.5 py-3 text-sm transition has-[:checked]:border-brand-600/40 has-[:checked]:bg-brand-50">
                    <input type="checkbox" wire:model.live="{{ $model }}" class="mt-0.5 h-4 w-4 rounded accent-brand-600">
                    <span class="text-ink/80">{{ $field['label'] }} @if ($field['consent'] ?? false) <span class="text-red-600">*</span> @endif</span>
                </label>
            @break

            @case('note')
                <p class="flex items-start gap-2 rounded-xl bg-gold-400/10 px-3.5 py-2.5 text-xs text-gold-700">
                    {!! \App\Support\Lucide::svg('Info', 2, 'h-4 w-4 shrink-0') !!}{{ $field['label'] }}
                </p>
            @break

            @case('zone_display')
                <p class="input flex items-center justify-between bg-cream text-ink/60">
                    Zon solat diambil dari Langkah 1.
                    <a href="{{ route('pic.step', ['token' => $token, 'step' => 1]) }}" class="text-brand-600 underline">Edit</a>
                </p>
            @break

            @case('upload')
                <input type="file" wire:model="files.{{ $rel }}"
                    class="input py-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
                @if (! empty($panelData[$field['key']]['name'] ?? null))
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-brand-600">
                        {!! \App\Support\Lucide::svg('Check', 2.5, 'h-3.5 w-3.5') !!}{{ $panelData[$field['key']]['name'] }}
                    </p>
                @endif
                <div wire:loading wire:target="files.{{ $rel }}" class="mt-1 text-xs text-gold-700">Memuat naik…</div>
                @error('files.' . $rel)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            @break

            @case('upload_multi')
                <input type="file" wire:model="files.{{ $rel }}"
                    class="input py-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-700">
                @php $uploaded = $panelData[$field['key']] ?? []; @endphp
                @if (! empty($uploaded))
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-brand-600">
                        {!! \App\Support\Lucide::svg('Check', 2.5, 'h-3.5 w-3.5') !!}{{ count($uploaded) }} fail dimuat naik
                    </p>
                @endif
                <div wire:loading wire:target="files.{{ $rel }}" class="mt-1 text-xs text-gold-700">Memuat naik…</div>
                @error('files.' . $rel)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            @break

            @case('facility_checklist')
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach (\App\Support\PageCatalog::facilities() as $fk => $flabel)
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-sand px-3 py-2 text-sm transition has-[:checked]:border-brand-600/40 has-[:checked]:bg-brand-50">
                            <input type="checkbox" wire:model.live="{{ $model }}" value="{{ $fk }}" class="h-4 w-4 rounded accent-brand-600"> {{ $flabel }}
                        </label>
                    @endforeach
                </div>
            @break

            @case('repeater_text')
                <div class="space-y-2">
                    @foreach ($panelData[$field['key']] ?? [] as $i => $row)
                        <div class="flex gap-2" wire:key="rt-{{ $rel }}-{{ $i }}">
                            <input type="text" wire:model.blur="data.{{ $rel }}.{{ $i }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="input flex-1">
                            <button type="button" wire:click="removeRow('{{ $rel }}', {{ $i }})"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-ink/40 transition hover:bg-red-50 hover:text-red-600">
                                {!! \App\Support\Lucide::svg('X', 2, 'h-4 w-4') !!}
                            </button>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addRow('{{ $rel }}')" class="flex items-center gap-1 text-sm font-medium text-brand-600 hover:underline">
                        {!! \App\Support\Lucide::svg('Plus', 2, 'h-4 w-4') !!} Tambah
                    </button>
                </div>
            @break

            @case('repeater')
                <div class="space-y-3">
                    @foreach ($panelData[$field['key']] ?? [] as $i => $row)
                        <div class="rounded-2xl border border-sand bg-cream p-4" wire:key="row-{{ $rel }}-{{ $i }}">
                            <div class="flex justify-end">
                                <button type="button" wire:click="removeRow('{{ $rel }}', {{ $i }})"
                                    class="flex items-center gap-1 text-xs font-medium text-red-600 hover:underline">
                                    {!! \App\Support\Lucide::svg('X', 2, 'h-3.5 w-3.5') !!} Buang baris
                                </button>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($field['item'] as $sub)
                                    @include('livewire.wizard.panels._field', ['field' => $sub, 'rel' => $rel . '.' . $i . '.' . $sub['key'], 'panelData' => $row])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    <div class="flex flex-wrap items-center gap-4">
                        @if (! isset($field['max']) || count($panelData[$field['key']] ?? []) < $field['max'])
                            <button type="button" wire:click="addRow('{{ $rel }}')" class="flex items-center gap-1 text-sm font-medium text-brand-600 hover:underline">
                                {!! \App\Support\Lucide::svg('Plus', 2, 'h-4 w-4') !!} Tambah baris
                            </button>
                        @endif
                        @if (($field['template'] ?? null) === 'faq')
                            <button type="button" wire:click="loadFaqCommon('{{ $rel }}')" class="text-sm font-medium text-gold-700 hover:underline">Muatkan 8 soalan lazim</button>
                        @endif
                    </div>
                </div>
            @break
        @endswitch

        @if ($field['pdpa'] ?? false)
            <p class="mt-2 flex items-start gap-2 rounded-xl bg-blue-50 px-3.5 py-2.5 text-xs text-blue-800">
                {!! \App\Support\Lucide::svg('Info', 2, 'h-4 w-4 shrink-0') !!}
                Pastikan setiap individu bersetuju nama &amp; gambar dipaparkan (PDPA).
            </p>
        @endif

        @error($model)
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
@endif
