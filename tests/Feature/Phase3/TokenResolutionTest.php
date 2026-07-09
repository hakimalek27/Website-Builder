<?php

use App\Models\Invitation;
use App\Models\Project;

// Fasa 3 — resolusi token PIC (§5.2, §11.1, §11.2).

it('resolves a valid token and updates access tracking', function () {
    $token = Invitation::generateToken();
    $project = Project::factory()->create(['mosque_name' => 'Masjid Token Sah']);
    $invitation = Invitation::factory()->for($project)->withToken($token)->create();

    $response = $this->get("/b/{$token}");

    $response->assertOk();
    $response->assertSee('Masjid Token Sah');

    $invitation->refresh();
    expect($invitation->opened_at)->not->toBeNull();
    expect($invitation->last_active_at)->not->toBeNull();
    expect($invitation->opens_count)->toBe(1);
});

it('rejects an expired token with a generic error page', function () {
    $token = Invitation::generateToken();
    Invitation::factory()->withToken($token)->expired()->create();

    $response = $this->get("/b/{$token}");

    $response->assertStatus(403);
    $response->assertSee('Pautan tidak sah atau telah luput');
});

it('rejects a revoked token with a generic error page', function () {
    $token = Invitation::generateToken();
    Invitation::factory()->withToken($token)->revoked()->create();

    $response = $this->get("/b/{$token}");

    $response->assertStatus(403);
    $response->assertSee('Pautan tidak sah atau telah luput');
});

it('shows an identical generic page for expired vs nonexistent (no leak, §5.2)', function () {
    $expiredToken = Invitation::generateToken();
    Invitation::factory()->withToken($expiredToken)->expired()->create();

    $expired = $this->get("/b/{$expiredToken}");
    $nonexistent = $this->get('/b/'.Invitation::generateToken());

    expect($expired->status())->toBe(403);
    expect($nonexistent->status())->toBe(403);
    // Mesej sama — tidak membezakan sebab.
    expect($expired->getContent())->toContain('Pautan tidak sah atau telah luput');
    expect($nonexistent->getContent())->toContain('Pautan tidak sah atau telah luput');
});

it('rate limits failed token resolution at 10/min/IP → 429 (§11.2)', function () {
    // 10 percubaan gagal → 403; ke-11 → 429.
    for ($i = 0; $i < 10; $i++) {
        $this->get('/b/'.Invitation::generateToken())->assertStatus(403);
    }

    $this->get('/b/'.Invitation::generateToken())->assertStatus(429);
});
