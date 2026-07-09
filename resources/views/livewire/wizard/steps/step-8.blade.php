{{-- Langkah 8 — Teknikal & Operasi (§6 L8) --}}
@php $inp = 'mt-1 w-full rounded-lg border border-[#EFE8DC] bg-white px-3 py-2 text-sm focus:border-[#1B5E3F] focus:ring-1 focus:ring-[#1B5E3F] outline-none'; @endphp
<div class="space-y-6">
    {{-- Domain --}}
    <div>
        <label class="block text-sm font-semibold">Status domain <span class="text-red-600">*</span></label>
        <div class="mt-2 space-y-1.5 text-sm">
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.domain_status" value="ada" class="text-[#1B5E3F]"> Sudah ada domain</label>
            @if (($data['domain_status'] ?? null) === 'ada')
                <div class="ml-6 space-y-2">
                    <input type="text" wire:model.blur="data.domain_name" placeholder="Nama domain (cth: masjidsaya.my)" class="{{ $inp }}">
                    <input type="text" wire:model.blur="data.registrar" placeholder="Registrar (cth: exabytes)" class="{{ $inp }}">
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="data.dns_access" class="text-[#1B5E3F]"> Ada akses DNS</label>
                </div>
            @endif
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.domain_status" value="belum" class="text-[#1B5E3F]"> Belum ada (perlu cadangan)</label>
            @if (($data['domain_status'] ?? null) === 'belum')
                <div class="ml-6">
                    <span class="text-xs text-[#1A1A1A]/60">Cadangan nama (max 3):</span>
                    @for ($i = 0; $i < 3; $i++)
                        <input type="text" wire:model.blur="data.domain_wishes.{{ $i }}" placeholder="Cth: masjidalfalah.my" class="{{ $inp }}">
                    @endfor
                </div>
            @endif
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.domain_status" value="gov_my" class="text-[#1B5E3F]"> .gov.my (melalui proses agensi — MYNIC)</label>
        </div>
        @error('data.domain_status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Laman sedia ada --}}
    <div>
        <label class="block text-sm font-medium">Laman web sedia ada</label>
        <input type="url" wire:model.blur="data.existing_site" placeholder="https://… (jika ada)" class="{{ $inp }}">
        <label class="mt-1 flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="data.migrate_content" class="text-[#1B5E3F]"> Pindahkan kandungan lama</label>
    </div>

    {{-- Emel rasmi --}}
    <div>
        <label class="block text-sm font-medium">E-mel rasmi (@domain)</label>
        <div class="mt-1 space-y-1 text-sm">
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.official_email_status" value="ada" class="text-[#1B5E3F]"> Sudah ada</label>
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.official_email_status" value="perlu" class="text-[#1B5E3F]"> Perlu (dibincang)</label>
        </div>
    </div>

    {{-- Hosting & penyelenggaraan --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Hosting</label>
            <div class="mt-1 space-y-1 text-sm">
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.hosting" value="urus_azan" class="text-[#1B5E3F]"> Diuruskan penyedia (lalai)</label>
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.hosting" value="sendiri" class="text-[#1B5E3F]"> Sendiri</label>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium">Penyelenggaraan</label>
            <div class="mt-1 space-y-1 text-sm">
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.maintenance" value="pakej_bulanan" class="text-[#1B5E3F]"> Pakej bulanan</label>
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.maintenance" value="sendiri" class="text-[#1B5E3F]"> Sendiri</label>
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.maintenance" value="bincang" class="text-[#1B5E3F]"> Bincang</label>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium">Sasaran tarikh live (pilihan)</label>
        <input type="date" wire:model.blur="data.target_live" class="{{ $inp }}">
    </div>
</div>
