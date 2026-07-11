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
        // putIfMissing (§Fasa 14) — seed semula selamat: nilai sedia ada (mis. kunci API
        // WhatsApp yang ditampal admin) TIDAK ditindih. Hanya kunci yang belum wujud diisi.

        // Nilai bukan sulit.
        Setting::putIfMissing('gen_cooldown_minutes', '5');
        Setting::putIfMissing('default_ai_quota', '3');
        Setting::putIfMissing('default_design_quota', '5');
        Setting::putIfMissing('invitation_default_days', '30');
        Setting::putIfMissing('admin_notify_email', config('reka.admin_notify_email'));
        Setting::putIfMissing('admin_notify_phone', '60189030363');
        Setting::putIfMissing('whatsapp_gateway_url', 'https://wassap.wehdah.my');
        Setting::putIfMissing('whatsapp_session_id', null);

        // Saluran draf HTML dua-peringkat (§Fasa 13) — lalai untuk produksi/dev.
        Setting::putIfMissing('draft_pipeline', 'html');
        Setting::putIfMissing('html_max_tokens', '30000');
        Setting::putIfMissing('qa_auto_polish', '1');   // §Fasa 15 — auto-polish 1× bila QA bawah piawai

        // Nilai sulit (kunci API gateway — ditampal melalui Tetapan admin, JANGAN commit).
        Setting::putIfMissing('whatsapp_api_key', null, encrypted: true);
    }
}
