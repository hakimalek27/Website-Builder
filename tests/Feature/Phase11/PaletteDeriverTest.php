<?php

use App\Support\PaletteDeriver;

// Fasa 11 — palet custom: terbitan HSL + kawalan kontras WCAG (§6 L2).

it('validates hex colour format', function () {
    expect(PaletteDeriver::isValidHex('#1B5E3F'))->toBeTrue()
        ->and(PaletteDeriver::isValidHex('#abc'))->toBeFalse()
        ->and(PaletteDeriver::isValidHex('1B5E3F'))->toBeFalse()
        ->and(PaletteDeriver::isValidHex(null))->toBeFalse()
        ->and(PaletteDeriver::isValidHex('#GGGGGG'))->toBeFalse();
});

it('computes correct WCAG contrast ratios', function () {
    expect(round(PaletteDeriver::contrastRatio('#000000', '#FFFFFF'), 1))->toBe(21.0)
        ->and(round(PaletteDeriver::contrastRatio('#FFFFFF', '#FFFFFF'), 1))->toBe(1.0);
});

it('derives six harmonious tokens with valid hex values', function () {
    $tokens = PaletteDeriver::derive('#1B5E3F', '#C9A961')['tokens'];

    foreach (['primary', 'primaryDark', 'accent', 'ink', 'bg', 'bgAlt'] as $key) {
        expect($tokens)->toHaveKey($key)
            ->and(PaletteDeriver::isValidHex($tokens[$key]))->toBeTrue();
    }
    expect($tokens['accent'])->toBe('#C9A961');   // aksen dikekalkan seadanya
});

it('darkens a too-light primary to stay readable (WCAG ≥ 4.5:1)', function () {
    $result = PaletteDeriver::derive('#FFE14D', '#333333');   // kuning cerah
    $tokens = $result['tokens'];

    expect($result['adjusted'])->toBeTrue()
        ->and(PaletteDeriver::contrastRatio('#FFFFFF', $tokens['primary']))->toBeGreaterThanOrEqual(4.5)
        ->and(PaletteDeriver::contrastRatio($tokens['primary'], $tokens['bg']))->toBeGreaterThanOrEqual(4.5);
});

it('leaves an already-dark primary essentially unchanged', function () {
    $result = PaletteDeriver::derive('#1B5E3F', '#C9A961');

    expect($result['adjusted'])->toBeFalse()
        ->and($result['tokens']['primary'])->toBe('#1B5E3F')
        ->and(PaletteDeriver::contrastRatio('#FFFFFF', $result['tokens']['primary']))->toBeGreaterThanOrEqual(4.5);
});
