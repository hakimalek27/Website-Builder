<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Nilai lalai Settings page (§5.3). WhatsApp gateway & kuota dalam DB (bukan env).
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Nilai bukan sulit.
        Setting::put('gen_cooldown_minutes', '5');
        Setting::put('default_ai_quota', '3');
        Setting::put('default_design_quota', '5');
        Setting::put('invitation_default_days', '30');
        Setting::put('admin_notify_email', config('reka.admin_notify_email'));
        Setting::put('whatsapp_gateway_url', null);

        // Nilai sulit (secret gateway WhatsApp).
        Setting::put('whatsapp_gateway_secret', null, encrypted: true);
    }
}
