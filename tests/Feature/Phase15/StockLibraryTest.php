<?php

use App\Support\StockLibrary;

// §Fasa 15 W2 — pustaka stok scene SVG crafted (lesen bersih, boleh-warna).

it('has a manifest where every scene file exists and carries a licence', function () {
    $scenes = StockLibrary::scenes();
    expect($scenes)->not->toBeEmpty();

    foreach ($scenes as $s) {
        expect($s)->toHaveKeys(['file', 'category', 'tier', 'license', 'author']);
        expect($s['license'])->not->toBeEmpty();
        expect(file_exists(resource_path('draft-kit/stock/'.$s['file'])))
            ->toBeTrue("Fail scene {$s['file']} tiada dalam draft-kit/stock");
    }
});

it('picks a scene deterministically by seed and category', function () {
    $a = StockLibrary::pick(12345, 'masjid-eksterior', 'masjid', 0);
    $b = StockLibrary::pick(12345, 'masjid-eksterior', 'masjid', 0);
    expect($a)->toBe($b);                               // deterministik
    expect($a['category'])->toBe('masjid-eksterior');
});

it('offsets slots so two heroes in one draft differ where possible', function () {
    $s0 = StockLibrary::pick(7, 'masjid-eksterior', 'masjid', 0);
    $s1 = StockLibrary::pick(7, 'masjid-eksterior', 'masjid', 1);
    // Dua scene eksterior wujud → slot berbeza patut bagi fail berbeza.
    expect($s0['file'])->not->toBe($s1['file']);
});

it('respects org kind — ngo does not receive mosque-only scenes', function () {
    $pick = StockLibrary::pick(3, 'masjid-interior', 'ngo', 0);
    // interior masjid tier=masjid → NGO fallback ke scene 'semua'/'ngo', bukan interior masjid.
    if ($pick !== null) {
        expect($pick['tier'])->not->toBe('masjid');
    }
});

it('tints a scene into a palette-coloured svg data-URI', function () {
    $uri = StockLibrary::sceneDataUri('masjid-subuh.svg', [
        'primary' => '#1D4E89', 'primaryDark' => '#10315C', 'accent' => '#B08D3E', 'bg' => '#F7FAFC',
    ]);

    expect($uri)->toStartWith('data:image/svg+xml,');
    // Token mentah TIDAK boleh bocor (semua diganti warna sebenar).
    $decoded = rawurldecode(substr($uri, strlen('data:image/svg+xml,')));
    expect($decoded)->not->toContain('__P__')->not->toContain('__AB__')->not->toContain('__PDEEP__');
    expect($decoded)->toContain('#1D4E89');            // primary disuntik
});

it('returns null tint for an unknown scene file', function () {
    expect(StockLibrary::sceneDataUri('tak-wujud.svg', []))->toBe('');
});
