{{-- Langkah 5 — Fungsi & Ciri (§6 L5) --}}
@php $inp = 'mt-1 w-full rounded-xl border border-sand bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/12 focus:outline-none'; @endphp
<div class="space-y-6">
    {{-- Payment gateway --}}
    <div>
        <label class="block text-sm font-semibold">Kaedah pembayaran / infaq</label>
        <div class="mt-2 space-y-1.5 text-sm">
            @foreach ([
                'toyyibpay' => 'ToyyibPay (dipakai ramai masjid; akaun mudah)',
                'billplz' => 'Billplz',
                'duitnow_qr_statik' => 'DuitNow QR statik (papar QR sahaja — paling ringkas)',
                'fpx_korporat' => 'FPX korporat (perlu akaun korporat bank — proses lebih lama)',
                'manual_bank' => 'Manual (papar no. akaun sahaja)',
            ] as $val => $label)
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.payment_gateway" value="{{ $val }}" class="h-4 w-4 accent-brand-600"> {{ $label }}</label>
            @endforeach
        </div>
        @error('data.payment_gateway') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <div class="mt-2">
            <span class="text-xs text-ink/60">Status akaun:</span>
            <label class="ml-2 text-sm"><input type="radio" wire:model.live="data.gateway_status" value="sudah_ada" class="h-4 w-4 accent-brand-600"> Sudah ada</label>
            <label class="ml-3 text-sm"><input type="radio" wire:model.live="data.gateway_status" value="belum" class="h-4 w-4 accent-brand-600"> Belum (perlu bantuan daftar)</label>
        </div>
    </div>

    {{-- WhatsApp & ciri --}}
    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="data.whatsapp_button" class="h-4 w-4 accent-brand-600"> Butang WhatsApp terapung</label>
        <input type="tel" wire:model.blur="data.wa_number" placeholder="No. WhatsApp (cth 60195998294)" class="{{ $inp }}">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="data.whatsapp_channel" class="h-4 w-4 accent-brand-600"> Saluran WhatsApp</label>
        <input type="url" wire:model.blur="data.wa_channel_url" placeholder="Pautan saluran WhatsApp" class="{{ $inp }}">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="data.add_to_calendar" class="h-4 w-4 accent-brand-600"> Tambah ke Kalendar (ICS) untuk program</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="data.bilingual" class="h-4 w-4 accent-brand-600"> Dwibahasa (BM + English)</label>
    </div>

    {{-- CMS updater (KRITIKAL) --}}
    <div class="rounded-xl border border-brand-600/30 bg-brand-50 p-4">
        <label class="block text-sm font-semibold">Siapa akan kemas kini kandungan? <span class="text-red-600">*</span></label>
        <div class="mt-2 space-y-1.5 text-sm">
            @foreach ([
                'ajk_sendiri' => 'AJK akan kemas kini sendiri → CMS (Sanity) dipasang',
                'urus_azan' => 'Diuruskan penyedia → pakej selenggara',
                'jarang' => 'Jarang berubah → laman statik',
            ] as $val => $label)
                <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.cms_updater" value="{{ $val }}" class="h-4 w-4 accent-brand-600"> {{ $label }}</label>
            @endforeach
        </div>
        @error('data.cms_updater') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Sistem kariah --}}
    <div>
        <label class="block text-sm font-semibold">Sistem kariah</label>
        <div class="mt-2 space-y-1.5 text-sm">
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.kariah_system" value="tiada" class="h-4 w-4 accent-brand-600"> Tiada</label>
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.kariah_system" value="pautan_sedia" class="h-4 w-4 accent-brand-600"> Pautan sedia ada</label>
            <input type="url" wire:model.blur="data.kariah_url" placeholder="Cth: ssda.mamkl.my" class="{{ $inp }}">
            <label class="flex items-center gap-2"><input type="radio" wire:model.live="data.kariah_system" value="perlu_bina" class="h-4 w-4 accent-brand-600"> Perlu bina (projek berasingan)</label>
        </div>
    </div>

    {{-- Flag backlog --}}
    <div class="rounded-xl border border-sand bg-cream p-4">
        <p class="text-xs font-medium text-gold-700">Ciri tambahan — akan dibincang (direkod sahaja)</p>
        <div class="mt-2 grid gap-2 sm:grid-cols-3 text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="data.tv_display" class="h-4 w-4 accent-brand-600"> Paparan TV</label>
            <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="data.pwa" class="h-4 w-4 accent-brand-600"> PWA</label>
            <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="data.wa_broadcast" class="h-4 w-4 accent-brand-600"> Hebahan WhatsApp</label>
        </div>
    </div>
</div>
