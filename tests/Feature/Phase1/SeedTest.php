<?php

use App\Models\DesignPackage;
use App\Models\JakimZone;
use App\Models\Setting;
use App\Support\PaletteDeriver;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\JakimZoneSeeder;
use Database\Seeders\SettingsSeeder;

// Fasa 1 — seed data rujukan (§16.A, §7.2, §5.3).

it('seeds exactly 59 JAKIM zones (§16.A)', function () {
    $this->seed(JakimZoneSeeder::class);

    expect(JakimZone::count())->toBe(59);
    // Format kod [A-Z]{3}0[1-9]; KTN tiada 02.
    expect(JakimZone::where('code', 'KTN02')->exists())->toBeFalse();
    expect(JakimZone::where('code', 'WLY01')->value('districts_label'))->toBe('Kuala Lumpur, Putrajaya');
});

it('seeds 14 design packages with exact tokens (§7.2 + Fasa 11)', function () {
    $this->seed(DesignPackageSeeder::class);

    expect(DesignPackage::count())->toBe(14);
    expect(DesignPackage::where('key', 'warisan_hijau')->value('tokens')['primary'])->toBe('#1B5E3F');
    expect(DesignPackage::where('key', 'marun_agung')->value('tokens')['accent'])->toBe('#C9A961');
    // Pakej baharu membawa varian struktur.
    expect(DesignPackage::where('key', 'nilam_senja')->value('variants')['header'])->toBe('gradien');
});

it('every design package palette meets WCAG contrast — primary readable (Fasa 11)', function () {
    $this->seed(DesignPackageSeeder::class);

    $fails = [];
    foreach (DesignPackage::all() as $pkg) {
        $onBg = PaletteDeriver::contrastRatio($pkg->tokens['primary'], $pkg->tokens['bg']);
        $onWhite = PaletteDeriver::contrastRatio('#FFFFFF', $pkg->tokens['primary']);
        if ($onBg < 4.5 || $onWhite < 4.5) {
            $fails[] = "{$pkg->key}: atas-bg=".round($onBg, 2).' teks-putih='.round($onWhite, 2);
        }
    }
    expect($fails)->toBe([]);
});

it('seeds default settings (§5.3)', function () {
    $this->seed(SettingsSeeder::class);

    expect(Setting::get('gen_cooldown_minutes'))->toBe('5');
    expect(Setting::get('default_ai_quota'))->toBe('3');
    expect(Setting::get('default_design_quota'))->toBe('5');
    expect(Setting::get('invitation_default_days'))->toBe('30');
    expect(Setting::whereIn('key', [
        'gen_cooldown_minutes', 'default_ai_quota', 'default_design_quota',
        'invitation_default_days', 'admin_notify_email', 'whatsapp_gateway_url',
        'whatsapp_gateway_secret',
    ])->count())->toBe(7);
});
