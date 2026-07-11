<?php

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;

// §Fasa 14 W1 — SettingsSeeder idempoten: seed semula tidak menindih konfigurasi admin.

it('seeds all default settings on a fresh database', function () {
    $this->seed(SettingsSeeder::class);

    expect(Setting::get('gen_cooldown_minutes'))->toBe('5');
    expect(Setting::get('draft_pipeline'))->toBe('template');
    expect(Setting::get('html_max_tokens'))->toBe('30000');
    expect(Setting::whereIn('key', [
        'gen_cooldown_minutes', 'default_ai_quota', 'default_design_quota',
        'invitation_default_days', 'admin_notify_email', 'admin_notify_phone',
        'whatsapp_gateway_url', 'whatsapp_session_id', 'draft_pipeline',
        'html_max_tokens', 'whatsapp_api_key',
    ])->count())->toBe(11);
});

it('does not overwrite existing admin configuration when re-seeded', function () {
    // Admin telah tampal kunci API & tukar saluran draf.
    Setting::put('whatsapp_api_key', 'kunci-rahsia-admin', encrypted: true);
    Setting::put('whatsapp_session_id', '60174627287');
    Setting::put('draft_pipeline', 'shell');

    $this->seed(SettingsSeeder::class);

    expect(Setting::get('whatsapp_api_key'))->toBe('kunci-rahsia-admin');
    expect(Setting::get('whatsapp_session_id'))->toBe('60174627287');
    expect(Setting::get('draft_pipeline'))->toBe('shell');
    // Kunci yang belum wujud tetap diisi.
    expect(Setting::get('gen_cooldown_minutes'))->toBe('5');
});

it('putIfMissing skips an existing null-valued row', function () {
    // Baris wujud tetapi bernilai null (admin kosongkan) — jangan tulis semula.
    Setting::put('whatsapp_session_id', null);
    Setting::putIfMissing('whatsapp_session_id', '60174627287');

    expect(Setting::get('whatsapp_session_id'))->toBeNull();
    expect(Setting::where('key', 'whatsapp_session_id')->count())->toBe(1);
});

it('putIfMissing writes a missing key and honours the encrypted flag', function () {
    Setting::putIfMissing('kunci_baharu', 'nilai-sulit', encrypted: true);

    $row = Setting::where('key', 'kunci_baharu')->first();
    expect($row)->not->toBeNull();
    expect($row->is_encrypted)->toBeTrue();
    expect($row->value)->not->toBe('nilai-sulit'); // disimpan tersulit
    expect(Setting::get('kunci_baharu'))->toBe('nilai-sulit'); // nyahsulit betul
});
