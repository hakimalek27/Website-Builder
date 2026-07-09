<?php

use App\Enums\ProjectStatus;
use App\Models\Invitation;
use App\Models\Project;
use App\Services\InvitationManager;
use Illuminate\Support\Facades\Notification;

// Fasa 3 — sweep expired (§4.2) & regenerate token (§11.1).

it('sweeps projects whose token expired into expired status (§4.2)', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::Invited]);
    Invitation::factory()->for($project)->expired()->create();

    // Projek lain dengan token sah — TIDAK disentuh.
    $active = Project::factory()->create(['status' => ProjectStatus::InProgress]);
    Invitation::factory()->for($active)->create(); // luput 30 hari akan datang

    $this->artisan('reka:sweep-expired')->assertSuccessful();

    expect($project->fresh()->status)->toBe(ProjectStatus::Expired);
    expect($active->fresh()->status)->toBe(ProjectStatus::InProgress);
});

it('regenerate replaces the token hash and the new token resolves (§11.1)', function () {
    Notification::fake();

    $oldToken = Invitation::generateToken();
    $project = Project::factory()->create(['mosque_name' => 'Masjid Regen']);
    $invitation = Invitation::factory()->for($project)->withToken($oldToken)->create();

    $oldHash = $invitation->token_hash;
    $newToken = app(InvitationManager::class)->regenerate($invitation);

    $invitation->refresh();
    expect($invitation->token_hash)->not->toBe($oldHash);
    expect($invitation->token_hash)->toBe(hash('sha256', $newToken));

    // Token lama tidak lagi sah; token baharu sah.
    $this->get("/b/{$oldToken}")->assertStatus(403);
    $this->get("/b/{$newToken}")->assertOk()->assertSee('Masjid Regen');
});
