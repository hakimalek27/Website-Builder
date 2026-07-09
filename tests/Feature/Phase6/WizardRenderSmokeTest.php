<?php

use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\JakimZoneSeeder;

// Fasa 6 — smoke: semua langkah 0–9 + semak dirender tanpa ralat blade.

it('renders every wizard step and the review page over HTTP', function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(JakimZoneSeeder::class);
    [$project, $token] = picSession();
    enablePages($project, ['utama', 'hubungi', 'infaq', 'galeri', 'nikah', 'kelas_quran', 'soalan_lazim', 'ajk', 'sejarah']);

    foreach (range(0, 9) as $step) {
        $this->get("/b/{$token}/langkah/{$step}")->assertOk();
    }

    $this->get("/b/{$token}/semak")->assertOk();
});
