<?php

use App\Models\JakimZone;
use Database\Seeders\JakimZoneSeeder;
use Illuminate\Support\Facades\Http;

// Fasa 1 — zones:verify menandakan verified_at (§16.A/§9.3).
// R12: ujian guna Http::fake — TIDAK memanggil e-Solat sebenar.

it('zones:verify marks zones verified when e-Solat returns OK!', function () {
    $this->seed(JakimZoneSeeder::class);

    Http::fake([
        '*e-solat.gov.my*' => Http::response([
            'prayerTime' => [[
                'hijri' => '1447-01-14', 'date' => '09-Jul-2026', 'day' => 'Khamis',
                'imsak' => '05:45:00', 'fajr' => '05:55:00', 'syuruk' => '07:10:00',
                'dhuhr' => '13:15:00', 'asr' => '16:38:00', 'maghrib' => '19:29:00', 'isha' => '20:42:00',
            ]],
            'status' => 'OK!',
            'zone' => 'WLY01',
        ], 200),
    ]);

    $this->artisan('zones:verify')->assertSuccessful();

    // Semua 59 zon ditanda verified_at.
    expect(JakimZone::whereNotNull('verified_at')->count())->toBe(59);
});

it('zones:verify fails (non-zero) when a zone response is not OK', function () {
    $this->seed(JakimZoneSeeder::class);

    Http::fake([
        '*e-solat.gov.my*' => Http::response(['status' => 'error'], 200),
    ]);

    $this->artisan('zones:verify')->assertFailed();
    expect(JakimZone::whereNotNull('verified_at')->count())->toBe(0);
});
