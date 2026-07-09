<?php

use App\Enums\ProjectStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Jobs\SendWhatsappJob;
use App\Mail\SubmittedMail;
use App\Models\Generation;
use App\Models\Invitation;
use App\Models\Lead;
use App\Models\Note;
use App\Models\NotificationLog;
use App\Models\Project;
use App\Models\Setting;
use App\Services\Notifier;
use App\Services\WhatsappGateway;
use App\Support\Mask;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

// Fasa 9 — notifikasi (§13), status PIC, admin, pengerasan (§11.3).

it('WhatsApp adapter posts to wassap.wehdah.my with X-API-Key (§13)', function () {
    Setting::put('whatsapp_gateway_url', 'https://wassap.wehdah.my');
    Setting::put('whatsapp_api_key', 'sk_test123', encrypted: true);
    Http::fake(['*wassap.wehdah.my*' => Http::response(['success' => true, 'data' => ['message_id' => 'm1']], 200)]);

    $ok = app(WhatsappGateway::class)->send('60195998294', 'Salam', null, 'test.event');

    expect($ok)->toBeTrue();
    Http::assertSent(fn ($request) => str_contains($request->url(), '/v1/messages/send')
        && $request->hasHeader('X-API-Key', 'sk_test123')
        && $request['to'] === '60195998294'
        && $request['message'] === 'Salam');
    $this->assertDatabaseHas('notification_logs', ['event' => 'test.event', 'channel' => 'whatsapp', 'status' => 'sent']);
});

it('normalizes 01x numbers to 601x msisdn', function () {
    Setting::put('whatsapp_gateway_url', 'https://wassap.wehdah.my');
    Http::fake(['*wassap.wehdah.my*' => Http::response(['success' => true], 200)]);

    app(WhatsappGateway::class)->send('0189030363', 'Hai', null, 'test.norm');

    Http::assertSent(fn ($request) => $request['to'] === '60189030363');
});

it('falls back to mail and logs when WhatsApp fails (§13)', function () {
    Mail::fake();
    Setting::put('whatsapp_gateway_url', 'https://wassap.wehdah.my');
    Http::fake(['*wassap.wehdah.my*' => Http::response('', 500)]);
    $project = Project::factory()->create();

    $ok = app(WhatsappGateway::class)->send('60123456789', 'Salam', $project->id, 'test.event');
    expect($ok)->toBeFalse();
    $this->assertDatabaseHas('notification_logs', ['event' => 'test.event', 'channel' => 'whatsapp', 'status' => 'failed']);

    // Fallback mail bila job gagal muktamad.
    (new SendWhatsappJob('60123456789', 'Salam', $project->id, 'test.event', new SubmittedMail($project), 'admin@reka.test'))->failed();
    Mail::assertQueued(SubmittedMail::class);
});

it('dispatches all twelve notification events (§13 + Fasa 11)', function () {
    Mail::fake();
    config()->set('reka.admin_notify_email', 'admin@reka.test');
    Setting::put('whatsapp_gateway_url', 'https://gw.example');
    Setting::put('admin_notify_phone', '60189030363');
    Http::fake(['*gw.example*' => Http::response(['success' => true], 200)]);

    $project = Project::factory()->create();
    Invitation::factory()->for($project)->create(['pic_phone' => '60123456789', 'pic_email' => 'pic@test.my']);
    $gen = Generation::factory()->for($project)->succeeded()->create();
    $lead = Lead::create(['mosque_name' => 'Lead X', 'org_type' => 'masjid', 'state' => 'Selangor', 'pic_name' => 'A', 'pic_phone' => '0123456789']);
    $note = Note::create(['project_id' => $project->id, 'author' => 'pic', 'author_name' => 'PIC', 'kind' => 'general', 'body' => 'Nota ujian']);

    $n = app(Notifier::class);
    $n->invitationSent($project, 'link');
    $n->wizardReminder($project, 'link');
    $n->submitted($project);
    $n->generationSucceeded($project, $gen, 'link');
    $n->generationFailed($gen);
    $n->quotaExhausted($project, 'nota');
    $n->approved($project);
    $n->buildUpdated($project, 'Live', 'link');
    $n->tokenExpiring($project);
    $n->leadReceived($lead);
    $n->noteAdded($project, $note);

    $events = NotificationLog::pluck('event')->unique()->values()->all();
    foreach ([
        'invitation.sent', 'wizard.reminder', 'submitted', 'generation.succeeded',
        'generation.failed', 'quota.exhausted_note', 'approved', 'status.build_updated',
        'token.expiring', 'lead.received', 'note.added',
    ] as $event) {
        expect($events)->toContain($event);
    }
});

it('sends wizard reminders at most twice (§13)', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::InProgress]);
    Invitation::factory()->for($project)->create(['last_active_at' => now()->subDays(10)]);

    $this->artisan('reka:reminders')->assertSuccessful();
    $this->artisan('reka:reminders')->assertSuccessful();
    $this->artisan('reka:reminders')->assertSuccessful();

    expect(NotificationLog::where('project_id', $project->id)->where('event', 'wizard.reminder')->count())->toBe(2);
});

it('supports a two-way notes thread (§5.2 P10)', function () {
    $token = Invitation::generateToken();
    $project = Project::factory()->create();
    Invitation::factory()->for($project)->withToken($token)->create(['pic_name' => 'Ali']);

    // PIC hantar nota (P11).
    $this->post("/b/{$token}/nota", ['body' => 'Nota dari PIC'])->assertRedirect();

    // Admin balas.
    Note::create(['project_id' => $project->id, 'author' => 'admin', 'author_name' => 'Azan', 'kind' => 'general', 'body' => 'Balasan admin']);

    expect(Note::where('project_id', $project->id)->where('author', 'pic')->exists())->toBeTrue();
    expect(Note::where('project_id', $project->id)->where('author', 'admin')->exists())->toBeTrue();
});

it('admin status changes respect valid transitions only (§4.2)', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::HandoverExported]);

    $project->transitionTo(ProjectStatus::InBuild, 'admin');
    expect($project->fresh()->status)->toBe(ProjectStatus::InBuild);

    // Lompatan tidak sah.
    expect(fn () => $project->fresh()->transitionTo(ProjectStatus::Live, 'admin'))
        ->toThrow(InvalidStatusTransitionException::class);
});

it('top-up increases the AI quota with an audit (§8.7)', function () {
    $project = Project::factory()->create(['quota_ai_total' => 3]);

    $project->topUpAiQuota(2);

    expect($project->fresh()->quota_ai_total)->toBe(5);
    $this->assertDatabaseHas('audit_logs', ['action' => 'quota.topup']);
});

it('mask helper never reveals full phone, email or token (§11.3)', function () {
    expect(Mask::phone('60195998294'))->toBe('6019•••294');
    expect(Mask::phone('60195998294'))->not->toContain('5998');
    expect(Mask::email('ahmad@example.com'))->toBe('a•••@example.com');
    expect(Mask::token('abcdef1234567890'))->toBe('abcdef…');
    expect(Mask::token('abcdef1234567890'))->not->toContain('1234567890');
});
