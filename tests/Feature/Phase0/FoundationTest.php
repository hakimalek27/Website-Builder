<?php

// Fasa 0 — Asas Projek (§15 baris Fasa 0).
// DoD: App boot, /admin login+2FA berfungsi, header hadir.

it('returns 200 for the landing health check', function () {
    // health check asas: halaman / membalas 200.
    $this->get('/')->assertOk();
});

it('sends security headers on every response (§11.3)', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('X-Frame-Options', 'DENY');
    // CSP asas juga hadir.
    $response->assertHeader('Content-Security-Policy');
});

it('requires authentication to access the admin panel', function () {
    // GET /admin (tanpa auth) → redirect ke login Filament.
    $response = $this->get('/admin');

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('/admin/login');
});
