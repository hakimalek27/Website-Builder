<?php

use App\Enums\ProjectStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Project;

// Fasa 1 — state machine §4.2 dikuatkuasa oleh Project::transitionTo().

it('allows valid status transitions and records audit', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::Invited]);

    $project->transitionTo(ProjectStatus::InProgress);
    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);

    $project->transitionTo(ProjectStatus::Submitted);
    expect($project->fresh()->status)->toBe(ProjectStatus::Submitted);
    expect($project->fresh()->submitted_at)->not->toBeNull();

    expect(AuditLog::where('action', 'project.status_changed')->count())->toBe(2);
});

it('throws on invalid status transitions and audits the rejection', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::Invited]);

    // invited → live TIDAK sah (§4.2).
    expect(fn () => $project->transitionTo(ProjectStatus::Live))
        ->toThrow(InvalidStatusTransitionException::class);

    expect($project->fresh()->status)->toBe(ProjectStatus::Invited);
    expect(AuditLog::where('action', 'project.status_change_rejected')->count())->toBe(1);
});

it('freezes wizard after approval (§4.2 titik beku)', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::DraftReady]);
    $project->transitionTo(ProjectStatus::Approved);

    expect($project->fresh()->isFrozen())->toBeTrue();
    // cancelled TIDAK dibenarkan selepas approved.
    expect(fn () => $project->fresh()->transitionTo(ProjectStatus::Cancelled))
        ->toThrow(InvalidStatusTransitionException::class);
});
