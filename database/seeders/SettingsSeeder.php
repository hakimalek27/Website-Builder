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
        Setting::put('admin_notify_phone', '60189030363');
        Setting::put('whatsapp_gateway_url', 'https://wassap.wehdah.my');
        Setting::put('whatsapp_session_id', null);

        // Saluran draf HTML dua-peringkat (§Fasa 13) — lalai untuk produksi/dev.
        Setting::put('draft_pipeline', 'html');
        Setting::put('html_max_tokens', '30000');

        // Nilai sulit (kunci API gateway — ditampal melalui Tetapan admin, JANGAN commit).
        Setting::put('whatsapp_api_key', null, encrypted: true);
    }
}
