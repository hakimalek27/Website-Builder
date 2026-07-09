<?php

use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Models\Invitation;
use App\Models\Lead;
use App\Notifications\InvitationNotification;
use App\Services\LeadQualifier;
use Illuminate\Support\Facades\Notification;

// Fasa 2 — kelayakan lead → Project + Invitation (§4.1 langkah 2, §11.1).

it('creates project and invitation, storing only the token hash (§11.1)', function () {
    Notification::fake();

    $lead = Lead::factory()->create([
        'mosque_name' => 'Masjid Layak Test',
        'state' => 'Selangor',
        'pic_name' => 'Rahim',
        'status' => LeadStatus::New,
    ]);

    $result = app(LeadQualifier::class)->qualify($lead, 'pic@example.com', 30, 3);

    $project = $result['project'];
    $token = $result['token'];

    // Projek dicipta, status invited, medan disalin.
    expect($project->status)->toBe(ProjectStatus::Invited);
    expect($project->mosque_name)->toBe('Masjid Layak Test');
    expect($project->quota_ai_total)->toBe(3);

    // Lead ditanda qualified + dipautkan.
    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified);
    expect($lead->fresh()->project_id)->toBe($project->id);

    // Token: plaintext 40 aksara; SIMPAN HASH SAHAJA.
    expect(strlen($token))->toBe(40);
    $invitation = Invitation::where('project_id', $project->id)->firstOrFail();
    expect($invitation->token_hash)->toBe(hash('sha256', $token));
    expect($invitation->token_hash)->not->toBe($token);
    // Tiada lajur menyimpan plaintext.
    expect($invitation->getAttributes())->not->toContain($token);
});

it('sends the invitation notification (§13 invitation.sent)', function () {
    Notification::fake();

    $lead = Lead::factory()->create(['status' => LeadStatus::New]);

    app(LeadQualifier::class)->qualify($lead, 'pic@example.com', 30, 3);

    Notification::assertSentOnDemand(InvitationNotification::class);
});

it('records audit events for qualify and invitation (§10)', function () {
    Notification::fake();

    $lead = Lead::factory()->create(['status' => LeadStatus::New]);
    app(LeadQualifier::class)->qualify($lead, 'pic@example.com', 30, 3);

    $this->assertDatabaseHas('audit_logs', ['action' => 'lead.qualified']);
    $this->assertDatabaseHas('audit_logs', ['action' => 'invitation.created']);
    $this->assertDatabaseHas('audit_logs', ['action' => 'invitation.sent']);
});
