<?php

use App\Models\Lead;
use Illuminate\Support\Facades\Mail;

// Fasa 2 — borang Daftar Minat (§5.1) + anti-spam (§11.2).

beforeEach(function () {
    Mail::fake();
});

it('validates and saves a valid lead, then redirects to thanks', function () {
    $response = $this->post('/minat', [
        'mosque_name' => 'Masjid Ujian Wangsa',
        'state' => 'Selangor',
        'pic_name' => 'Ahmad bin Ali',
        'pic_phone' => '0123456789',
        'pic_email' => 'ahmad@example.com',
        'notes' => 'Kami berminat.',
    ]);

    $response->assertRedirect(route('minat.terima-kasih'));
    $this->assertDatabaseHas('leads', [
        'mosque_name' => 'Masjid Ujian Wangsa',
        'state' => 'Selangor',
        'status' => 'new',
    ]);
});

it('rejects invalid phone and missing required fields', function () {
    $response = $this->from('/minat')->post('/minat', [
        'mosque_name' => '',
        'state' => 'BukanNegeri',
        'pic_name' => 'A',
        'pic_phone' => '999', // bukan format 01x
    ]);

    $response->assertSessionHasErrors(['mosque_name', 'state', 'pic_phone']);
    expect(Lead::count())->toBe(0);
});

it('silently drops honeypot submissions without saving (§5.1)', function () {
    $response = $this->post('/minat', [
        'mosque_name' => 'Masjid Spam',
        'state' => 'Selangor',
        'pic_name' => 'Bot',
        'pic_phone' => '0123456789',
        'website_url' => 'http://spam.example', // honeypot terisi
    ]);

    $response->assertRedirect(route('minat.terima-kasih'));
    expect(Lead::count())->toBe(0);
});

it('rate limits the minat POST to 5 per minute (§11.2)', function () {
    $payload = [
        'mosque_name' => 'Masjid Kadar',
        'state' => 'Selangor',
        'pic_name' => 'Siti',
        'pic_phone' => '0123456789',
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->post('/minat', $payload)->assertRedirect();
    }

    // Permintaan ke-6 → 429.
    $this->post('/minat', $payload)->assertStatus(429);
});

it('skips Turnstile when not configured (§5.1)', function () {
    config()->set('reka.turnstile.site_key', null);
    config()->set('reka.turnstile.secret_key', null);

    $response = $this->post('/minat', [
        'mosque_name' => 'Masjid Tanpa Turnstile',
        'state' => 'Kedah',
        'pic_name' => 'Zaki',
        'pic_phone' => '0198887777',
    ]);

    $response->assertRedirect(route('minat.terima-kasih'));
    $this->assertDatabaseHas('leads', ['mosque_name' => 'Masjid Tanpa Turnstile']);
});
