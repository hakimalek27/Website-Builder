<?php

use App\Enums\AiDriver;
use App\Enums\GenerationStatus;
use App\Enums\ProjectStatus;
use App\Models\AiProvider;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Models\NotificationLog;
use App\Models\ProjectSection;
use App\Models\Setting;
use App\Services\ApprovalService;
use App\Services\DesignRerenderService;
use App\Services\HandoverExporter;
use App\Services\LeadQualifier;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\JakimZoneSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

// Fasa 10 — QA hujung-ke-hujung: lead → qualify → wizard → hantar → jana → tweak → lulus → eksport.

it('runs the full funnel end to end and exports a valid handover package', function () {
    Mail::fake();
    Notification::fake();
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
    $this->seed(JakimZoneSeeder::class);
    $this->seed(SettingsSeeder::class);
    Setting::put('draft_pipeline', 'shell'); // aliran ini menguji shell → spec.json → handover (§Fasa 13)
    config()->set('reka.admin_notify_email', 'admin@reka.test');

    // 1. Lead (borang minat awam).
    $this->post('/minat', [
        'mosque_name' => 'Surau E2E', 'org_type' => 'surau', 'state' => 'W.P. Kuala Lumpur',
        'pic_name' => 'Ahmad', 'pic_phone' => '0123456789', 'pic_email' => 'ahmad@test.my',
    ])->assertRedirect(route('minat.terima-kasih'));
    $lead = Lead::where('mosque_name', 'Surau E2E')->firstOrFail();

    // 2. Qualify → project + invitation + token.
    $result = app(LeadQualifier::class)->qualify($lead, 'ahmad@test.my', 30, 3);
    $project = $result['project'];
    $token = $result['token'];
    $project->update(['jakim_zone' => 'WLY01']);

    // 3. Buka token (P1).
    $this->get("/b/{$token}")->assertOk();

    // 4. Isi wizard (semua medan wajib) + halaman.
    enablePages($project, ['utama', 'hubungi', 'infaq']);
    foreach ([
        'step_0' => ['tier' => 'surau_ringkas'],
        'step_1' => ['official_name' => 'Surau E2E', 'address_line1' => 'Jln 1', 'postcode' => '53300', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01', 'authority' => 'MAIWP', 'gps' => '3.19, 101.73', 'phone_primary' => '0341491818', 'email' => 'surau@test.my', 'logo_status' => 'teks_sahaja'],
        'step_2' => ['mood' => 'mesra_keluarga', 'design_package' => 'warisan_hijau'],
        'step_4' => ['panels' => ['hubungi' => ['form_recipient_email' => 'surau@test.my'], 'infaq' => ['bank_name' => 'Maybank', 'bank_account' => '1234567890', 'account_holder' => 'Surau E2E', 'categories' => [['title' => 'Infaq Am']]]]],
        'step_5' => ['cms_updater' => 'urus_azan', 'payment_gateway' => 'manual_bank'],
        'step_6' => ['hero_mode' => 'stok_sementara'],
        'step_8' => ['domain_status' => 'belum'],
        'step_9' => ['pic_name' => 'Ahmad', 'pic_position' => 'Setiausaha', 'pic_phone' => '0123456789', 'consent_pdpa' => true, 'declare_truth_authority' => true],
    ] as $key => $data) {
        ProjectSection::create(['project_id' => $project->id, 'section_key' => $key, 'data' => $data]);
    }
    $project->update(['status' => ProjectStatus::InProgress]);

    // 5. Sedia AI (Http::fake) SEBELUM hantar — draf dijana AUTOMATIK selepas hantar (Fasa 11).
    AiProvider::factory()->default()->create(['driver' => AiDriver::Anthropic]);
    $content = validContent();
    $content['infaq'] = ['heading' => 'Infaq', 'paragraph' => 'Sumbangan anda.'];
    fakeAnthropic($content);

    $this->post("/b/{$token}/hantar")->assertSessionHas('success');
    expect($project->fresh()->status)->toBe(ProjectStatus::DraftReady);   // auto-jana (queue sync)
    $gen = $project->fresh()->generations()->where('status', GenerationStatus::Succeeded)->first();

    // 7. Lihat draf (P5) + watermark.
    $this->get("/b/{$token}/draf/{$gen->id}")->assertOk()->assertSee('DRAF SAMPEL');

    // 8. Tweak reka bentuk (tiada AI).
    app(DesignRerenderService::class)->rerender($project);
    expect($project->fresh()->quota_design_used)->toBe(1);
    expect($project->fresh()->quota_ai_used)->toBe(1); // AI tidak disentuh oleh tweak reka

    // 9. Lulus (snapshot beku).
    $project = $project->fresh();
    $latest = $project->generations()->where('status', GenerationStatus::Succeeded)->latest()->first();
    app(ApprovalService::class)->approve($project, $latest, [
        'pic_name' => 'Ahmad', 'pic_position' => 'Setiausaha', 'pic_phone' => '0123456789',
    ], '203.0.113.10', 'E2E');
    expect($project->fresh()->status)->toBe(ProjectStatus::Approved);

    // 10. Eksport pakej serahan.
    $export = app(HandoverExporter::class)->export($project);
    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($export->zip_path));
    $names = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $names[] = $zip->getNameIndex($i);
    }
    $zip->close();
    expect($names)->toContain('spec.json', 'build-brief.md', 'content/sanity-seed.ndjson', 'draft/approved-draft.html', 'README-HANDOVER.md');

    // 11. Notifikasi & audit terhasil.
    expect(NotificationLog::count())->toBeGreaterThan(0);
    expect(AuditLog::whereIn('action', ['lead.qualified', 'project.submitted', 'generation.succeeded', 'approval.recorded', 'handover.exported'])->count())->toBeGreaterThanOrEqual(5);
});
