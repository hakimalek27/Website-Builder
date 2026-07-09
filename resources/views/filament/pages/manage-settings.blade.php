<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6 max-w-2xl">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="text-sm">Gateway WhatsApp URL
                <input type="url" wire:model="whatsapp_gateway_url" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
            </label>
            <label class="text-sm">Gateway Secret (disulitkan)
                <input type="password" wire:model="whatsapp_gateway_secret" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
            </label>
            <label class="text-sm">Cooldown jana (minit)
                <input type="number" wire:model="gen_cooldown_minutes" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
            </label>
            <label class="text-sm">Kuota AI lalai
                <input type="number" wire:model="default_ai_quota" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
            </label>
            <label class="text-sm">Kuota render reka bentuk lalai
                <input type="number" wire:model="default_design_quota" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
            </label>
            <label class="text-sm">Tempoh token lalai (hari)
                <input type="number" wire:model="invitation_default_days" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
            </label>
            <label class="text-sm sm:col-span-2">E-mel notifikasi admin
                <input type="email" wire:model="admin_notify_email" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">
            </label>
        </div>

        <x-filament::button type="submit">Simpan Tetapan</x-filament::button>
    </form>
</x-filament-panels::page>
