<?php

use App\Support\DraftKit;

// §Fasa 15 W1 — kit CSS premium disuntik pelayan (0 token AI).

it('builds a reka-kit style tag with root tokens and variant classes', function () {
    $style = DraftKit::styleTag([
        'tokens' => ['primary' => '#1D4E89', 'primaryDark' => '#10315C', 'accent' => '#B08D3E', 'ink' => '#16202B', 'bg' => '#F7FAFC', 'bgAlt' => '#E8EFF5', 'radius' => '1rem'],
        'fonts' => ['body' => 'Inter', 'display' => 'Playfair Display', 'arabic' => 'Amiri'],
    ]);

    expect($style)
        ->toContain('<style id="reka-kit">')
        ->toContain(':root{')
        ->toContain('--rk-primary:#1D4E89')
        ->toContain('--rk-accent-bright:')       // ramp disuntik
        ->toContain('--rk-shadow-tint:')
        ->toContain('.rk-card--lembut')
        ->toContain('.rk-card--garis')
        ->toContain('.rk-card--terapung')
        ->toContain('.rk-hero--mihrab')
        ->toContain('.rk-eyebrow')
        ->toContain('.rk-ornament')
        ->toContain('.rk-prayer__grid')
        ->toContain("--rk-font-body:'Inter'");
});

it('embeds islamic patterns as inline svg data-URIs (no external fetch)', function () {
    $style = DraftKit::styleTag(['tokens' => ['accent' => '#C9A961', 'primary' => '#1B5E3F']]);

    expect($style)
        ->toContain('--rk-pattern-dots:url("data:image/svg+xml,')
        ->toContain('--rk-pattern-rub:url("data:image/svg+xml,');
    // TIADA muat turun luar — corak semuanya data-URI, bukan url(http…).
    expect($style)->not->toContain('url("http')->not->toContain('url(http');
});

it('keeps the minified kit css within budget and free of external urls', function () {
    $css = DraftKit::kitCss();

    expect(strlen($css))->toBeLessThan(20000);          // bajet kit ≤ 20KB
    expect($css)->not->toContain('http://')->not->toContain('https://');
    expect($css)->not->toContain('/*');                 // komen dibuang (minified)
    expect($css)->toContain('clamp(');                  // skala jenis bendalir kekal
});

it('preserves calc/clamp operator spaces after minification', function () {
    $css = DraftKit::kitCss();
    // Ruang sekitar '+' dalam clamp WAJIB kekal (else CSS tak sah).
    expect($css)->toContain('5vw + 0.5rem');
});
