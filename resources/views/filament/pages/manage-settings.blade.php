<x-filament-panels::page>
    <form wire:submit="save" class="max-w-2xl space-y-8">
        {{-- Seksyen 1: WhatsApp --}}
        <section class="space-y-4">
            <div>
                <h3 class="text-base font-semibold">Gateway WhatsApp</h3>
                <p class="text-sm text-gray-500">Sambungan ke wassap.wehdah.my untuk makluman lead, hantaran borang & nota.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="text-sm">URL Gateway (asas)
                    <input type="url" wire:model="whatsapp_gateway_url" placeholder="https://wassap.wehdah.my" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
                <label class="text-sm">Kunci API (X-API-Key)
                    <input type="password" wire:model="whatsapp_api_key" placeholder="Biar kosong untuk kekalkan" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                    <span class="mt-1 block text-xs text-gray-400">Ditampal sekali; kosong = tidak diubah.</span>
                </label>
                <label class="text-sm">ID Sesi Penghantar
                    <input type="text" wire:model="whatsapp_session_id" placeholder="Peranti 60174627287 (pilihan)" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
                <label class="text-sm">Telefon Admin (notifikasi)
                    <input type="text" wire:model="admin_notify_phone" placeholder="60189030363" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
            </div>
            <x-filament::button type="button" color="gray" wire:click="testWhatsapp" wire:loading.attr="disabled">
                Uji Hantar WhatsApp
            </x-filament::button>
        </section>

        {{-- Seksyen 2: Penjanaan & kuota --}}
        <section class="space-y-4">
            <div>
                <h3 class="text-base font-semibold">Penjanaan &amp; Kuota</h3>
                <p class="text-sm text-gray-500">Kawalan cooldown penjanaan draf & kuota lalai projek baharu.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="text-sm">Cooldown jana (minit)
                    <input type="number" min="0" wire:model="gen_cooldown_minutes" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
                <label class="text-sm">Kuota AI lalai
                    <input type="number" min="1" wire:model="default_ai_quota" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
                <label class="text-sm">Kuota render reka lalai
                    <input type="number" min="1" wire:model="default_design_quota" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
            </div>
        </section>

        {{-- Seksyen 3: Jemputan & notifikasi --}}
        <section class="space-y-4">
            <div>
                <h3 class="text-base font-semibold">Jemputan &amp; Notifikasi</h3>
                <p class="text-sm text-gray-500">Tempoh sah token jemputan & e-mel admin (fallback bila WhatsApp gagal).</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="text-sm">Tempoh token lalai (hari)
                    <input type="number" min="1" wire:model="invitation_default_days" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
                <label class="text-sm">E-mel notifikasi admin
                    <input type="email" wire:model="admin_notify_email" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
                </label>
            </div>
        </section>

        <x-filament::button type="submit">Simpan Tetapan</x-filament::button>
    </form>
</x-filament-panels::page>
