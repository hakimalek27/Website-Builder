<?php

use App\Models\DesignPackage;
use App\Support\PaletteDeriver;
use Database\Seeders\DesignPackageSeeder;

// §Fasa 15 W1 — ramp warna 7-peranan untuk Kit Reka Premium (WCAG dijamin).

it('derives five extended roles from base tokens', function () {
    $ramp = PaletteDeriver::ramp([
        'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961', 'bg' => '#FAF7F2',
    ]);

    expect($ramp)->toHaveKeys(['primaryDeep', 'primaryLight', 'accentBright', 'accentDeep', 'shadowTint']);
    expect($ramp['primaryDeep'])->toMatch('/^#[0-9A-F]{6}$/');
    expect($ramp['shadowTint'])->toMatch('/^\d+ \d+ \d+$/');   // "r g b" untuk rgb(<tint> / a)
});

it('guarantees accentBright is readable ON primaryDark for every seeded package', function () {
    (new DesignPackageSeeder)->run();

    foreach (DesignPackage::all() as $pkg) {
        $t = $pkg->tokens;
        $ramp = PaletteDeriver::ramp($t);
        $ratio = PaletteDeriver::contrastRatio($ramp['accentBright'], $t['primaryDark']);
        expect($ratio)->toBeGreaterThanOrEqual(4.5, "accentBright pakej {$pkg->key} hanya {$ratio}:1 atas primaryDark");
    }
});

it('guarantees accentDeep is readable ON bg for every seeded package', function () {
    (new DesignPackageSeeder)->run();

    foreach (DesignPackage::all() as $pkg) {
        $t = $pkg->tokens;
        $ramp = PaletteDeriver::ramp($t);
        $ratio = PaletteDeriver::contrastRatio($ramp['accentDeep'], $t['bg']);
        expect($ratio)->toBeGreaterThanOrEqual(4.5, "accentDeep pakej {$pkg->key} hanya {$ratio}:1 atas bg");
    }
});

it('handles an extreme custom palette without breaking the WCAG guarantee', function () {
    // Kuning terang atas hijau sangat gelap — kes tepi.
    $ramp = PaletteDeriver::ramp([
        'primary' => '#0A3D2A', 'primaryDark' => '#04140D', 'accent' => '#FFF3B0', 'bg' => '#FFFFFF',
    ]);

    expect(PaletteDeriver::contrastRatio($ramp['accentBright'], '#04140D'))->toBeGreaterThanOrEqual(4.5);
    expect(PaletteDeriver::contrastRatio($ramp['accentDeep'], '#FFFFFF'))->toBeGreaterThanOrEqual(4.5);
});

it('falls back to sane defaults for missing tokens', function () {
    $ramp = PaletteDeriver::ramp([]);   // tiada token → default warisan_hijau
    expect($ramp['primaryDeep'])->toMatch('/^#[0-9A-F]{6}$/');
    expect($ramp['accentBright'])->toMatch('/^#[0-9A-F]{6}$/');
});

it('leaves the legacy derive() output shape untouched (regression)', function () {
    $out = PaletteDeriver::derive('#1B5E3F', '#C9A961');
    expect($out)->toHaveKeys(['tokens', 'adjusted']);
    expect($out['tokens'])->toHaveKeys(['primary', 'primaryDark', 'accent', 'ink', 'bg', 'bgAlt']);
});
