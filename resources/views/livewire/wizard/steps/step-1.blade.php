{{-- Langkah 1 — Maklumat Asas Masjid (§6 L1) --}}
@php $inp = 'mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2.5 text-sm focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none'; @endphp
<div class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium">Nama rasmi masjid <span class="text-red-600">*</span></label>
            <input type="text" wire:model.blur="data.official_name" maxlength="150"
                   placeholder="Cth: Masjid Al-Muttaqin Wangsa Melawati" class="{{ $inp }}">
            @error('data.official_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Nama pendek</label>
            <input type="text" wire:model.blur="data.short_name" maxlength="40" placeholder="Cth: MAM" class="{{ $inp }}">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium">Alamat baris 1 <span class="text-red-600">*</span></label>
            <input type="text" wire:model.blur="data.address_line1" class="{{ $inp }}">
            @error('data.address_line1') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium">Alamat baris 2</label>
            <input type="text" wire:model.blur="data.address_line2" class="{{ $inp }}">
        </div>
        <div>
            <label class="block text-sm font-medium">Poskod <span class="text-red-600">*</span></label>
            <input type="text" wire:model.blur="data.postcode" maxlength="5" placeholder="53300" class="{{ $inp }}">
            @error('data.postcode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Bandar <span class="text-red-600">*</span></label>
            <input type="text" wire:model.blur="data.city" class="{{ $inp }}">
            @error('data.city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Negeri <span class="text-red-600">*</span></label>
            <select wire:model.live="data.state" class="{{ $inp }}">
                <option value="">— Pilih —</option>
                @foreach (config('reka.states') as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                @endforeach
            </select>
            @error('data.state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Zon solat JAKIM <span class="text-red-600">*</span></label>
            <select wire:model.blur="data.jakim_zone" class="{{ $inp }}">
                <option value="">— Pilih negeri dahulu —</option>
                @foreach ($this->zoneOptions() as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-[#1A1A1A]/55">Zon menentukan waktu solat rasmi JAKIM di laman anda.</p>
            @error('data.jakim_zone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Pihak berkuasa agama <span class="text-red-600">*</span></label>
            <input type="text" wire:model.blur="data.authority" list="authority-list" class="{{ $inp }}">
            <datalist id="authority-list">
                @foreach (['MAIWP','JAWI','JAIS','MAIS','JAIJ','MAIJ','JAIM','MAIM','JAIPk','JAIPP','MAINPP','MUIP','MAIK','MAIDAM','MAINS','MAIPs'] as $a)
                    <option value="{{ $a }}">
                @endforeach
            </datalist>
            @error('data.authority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium">Tahun ditubuhkan</label>
                <input type="number" wire:model.blur="data.established_year" min="1800" max="2026" placeholder="1987" class="{{ $inp }}">
                @error('data.established_year') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Kapasiti jemaah</label>
                <input type="number" wire:model.blur="data.capacity" min="1" placeholder="1500" class="{{ $inp }}">
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium">Koordinat GPS <span class="text-red-600">*</span></label>
        <input type="text" wire:model.blur="data.gps" placeholder="3.1985, 101.7308" class="{{ $inp }}">
        <p class="mt-1 text-xs text-[#1A1A1A]/55">Buka Google Maps → tekan lama pada masjid → salin koordinat (cth: 3.1985, 101.7308).</p>
        @error('data.gps') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label class="block text-sm font-medium">Telefon utama <span class="text-red-600">*</span></label>
            <input type="tel" wire:model.blur="data.phone_primary" placeholder="03-41491818" class="{{ $inp }}">
            @error('data.phone_primary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Telefon kedua</label>
            <input type="tel" wire:model.blur="data.phone_secondary" class="{{ $inp }}">
        </div>
        <div>
            <label class="block text-sm font-medium">E-mel <span class="text-red-600">*</span></label>
            <input type="email" wire:model.blur="data.email" class="{{ $inp }}">
            @error('data.email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <input type="url" wire:model.blur="data.facebook_url" placeholder="Facebook (pautan)" class="{{ $inp }}">
        <input type="url" wire:model.blur="data.instagram_url" placeholder="Instagram (pautan)" class="{{ $inp }}">
        <input type="url" wire:model.blur="data.youtube_url" placeholder="YouTube (pautan)" class="{{ $inp }}">
        <input type="url" wire:model.blur="data.tiktok_url" placeholder="TikTok (pautan)" class="{{ $inp }}">
    </div>

    {{-- Logo (§6 L1). Upload penuh + re-encode = Fasa 6 (TODO-F6). --}}
    <div>
        <label class="block text-sm font-medium">Logo masjid <span class="text-red-600">*</span></label>
        <div class="mt-2 space-y-2">
            @foreach ([
                'ada' => 'Sudah ada logo (akan dimuat naik)',
                'perlu_direka' => 'Perlu direka (kos tambahan — akan dibincang)',
                'teks_sahaja' => 'Guna nama masjid bergaya sebagai logo',
            ] as $val => $label)
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" wire:model.live="data.logo_status" value="{{ $val }}" class="text-[#1B5E3F] focus:ring-[#1B5E3F]">
                    {{ $label }}
                </label>
            @endforeach
        </div>
        @if (($data['logo_status'] ?? null) === 'ada')
            <p class="mt-2 text-xs text-[#8C6D2F]">Muat naik logo akan diaktifkan pada langkah Media (png/svg/jpg, ≤4MB).</p>
        @endif
        @error('data.logo_status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
