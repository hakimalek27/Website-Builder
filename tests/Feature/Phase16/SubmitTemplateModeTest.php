<?php

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Jobs\GenerateDraftJob;
use App\Jobs\SendWhatsappJob;
use App\Mail\SubmittedMail;
use App\Models\ProjectSection;
use App\Models\Setting;
use App\Models\TemplateCatalog;
use App\Services\CompletenessService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

// §Fasa 16 W4 — submit mod templat (tiada AI) + laluan PIC pasca-hantar.

/** Projek surau LENGKAP dalam mod templat (mood + templat rujukan). @return array{0: \App\Models\Project, 1: string, 2: TemplateCatalog} */
function fillCompleteTemplateProject(): array
{
    Setting::put('draft_pipeline', 'template');
    $tpl = TemplateCatalog::factory()->create(['name' => 'Masjid Rujukan', 'categories' => ['masjid']]);

    [$project, $token] = picSession(['tier' => Tier::SurauRingkas, 'status' => ProjectStatus::InProgress]);
    enablePages($project, ['utama', 'waktu_solat', 'hubungi', 'pengumuman', 'infaq', 'soalan_lazim', 'kuliah_mingguan']);

    $sections = [
        'step_0' => ['tier' => 'surau_ringkas'],
        'step_1' => [
            'official_name' => 'Surau Ujian', 'address_line1' => 'Jalan 1', 'postcode' => '53300',
            'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01', 'authority' => 'MAIWP',
            'gps' => '3.19, 101.73', 'phone_primary' => '0341491818', 'email' => 'surau@test.my', 'logo_status' => 'teks_sahaja',
        ],
        'step_2' => [
            'mood' => 'tenang_khusyuk', 'template_id' => $tpl->id,
            'template_snapshot' => ['name' => 'Masjid Rujukan', 'url' => 'https://x.test/t', 'demo_url' => null],
        ],
        'step_4' => ['panels' => [
            'hubungi' => ['form_recipient_email' => 'surau@test.my'],
            'infaq' => ['bank_name' => 'Maybank', 'bank_account' => '1234567890', 'account_holder' => 'Surau Ujian'],
        ]],
        'step_5' => ['cms_updater' => 'urus_azan', 'payment_gateway' => 'manual_bank'],
        'step_6' => ['hero_mode' => 'stok_sementara'],
        'step_8' => ['domain_status' => 'belum'],
        'step_9' => [
            'pic_name' => 'Ali', 'pic_position' => 'Setiausaha', 'pic_phone' => '0123456789',
            'consent_pdpa' => true, 'declare_truth_authority' => true,
        ],
    ];
    foreach ($sections as $key => $data) {
        ProjectSection::create(['project_id' => $project->id, 'section_key' => $key, 'data' => $data]);
    }

    return [$project, $token, $tpl];
}

it('reaches 100% completeness in template mode with mood + template', function () {
    [$project] = fillCompleteTemplateProject();

    expect(app(CompletenessService::class)->compute($project)['score'])->toBe(100);
});

it('flags template choice as missing when neither template nor custom url is given', function () {
    Setting::put('draft_pipeline', 'template');
    [$project] = fillCompleteTemplateProject();
    // Buang pilihan templat.
    ProjectSection::where('project_id', $project->id)->where('section_key', 'step_2')
        ->update(['data' => ['mood' => 'tenang_khusyuk']]);

    $result = app(CompletenessService::class)->compute($project);
    expect($result['score'])->toBeLessThan(100);
    expect(collect($result['missing'])->pluck('key'))->toContain('template_choice');
});

it('submits in template mode without generating an AI draft', function () {
    Mail::fake();
    Queue::fake();
    [$project, $token] = fillCompleteTemplateProject();

    $this->post("/b/{$token}/hantar")
        ->assertRedirect(route('pic.status', ['token' => $token]))
        ->assertSessionHas('success');

    expect($project->fresh()->status)->toBe(ProjectStatus::Submitted);
    Mail::assertQueued(SubmittedMail::class);
    Queue::assertNotPushed(GenerateDraftJob::class);
});

it('sends admin + PIC WhatsApp with the template name on submit', function () {
    Queue::fake();
    Setting::put('admin_notify_phone', '60189030363');
    [$project, $token] = fillCompleteTemplateProject();
    $project->invitation()->update(['pic_phone' => '60123456789']);

    $this->post("/b/{$token}/hantar");

    Queue::assertPushed(SendWhatsappJob::class, fn ($job) => str_contains($job->message, 'MOD TEMPLAT') && str_contains($job->message, 'Masjid Rujukan'));
    Queue::assertPushed(SendWhatsappJob::class, fn ($job) => str_contains($job->message, 'akan membina laman anda'));
});

it('redirects the Jana Draf page to status in template mode', function () {
    Setting::put('draft_pipeline', 'template');
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted]);

    $this->get("/b/{$token}/jana")->assertRedirect(route('pic.status', ['token' => $token]));
});

it('redirects content tweak to status in template mode', function () {
    Setting::put('draft_pipeline', 'template');
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted]);

    $this->get("/b/{$token}/tweak/kandungan")->assertRedirect(route('pic.status', ['token' => $token]));
});

it('allows the Submitted to InBuild transition (no draft/approval needed)', function () {
    [$project] = picSession(['status' => ProjectStatus::Submitted]);

    $project->transitionTo(ProjectStatus::InBuild, 'admin');

    expect($project->fresh()->status)->toBe(ProjectStatus::InBuild);
});
