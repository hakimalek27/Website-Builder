<?php

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Jobs\GenerateDraftJob;
use App\Models\ProjectSection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

// Fasa 11 — auto-jana draf selepas hantar (§I).

function fillCompleteFor(string $logoStatus = 'teks_sahaja', string $heroMode = 'stok_sementara'): array
{
    [$project, $token] = picSession(['tier' => Tier::SurauRingkas, 'status' => ProjectStatus::InProgress]);
    enablePages($project, ['utama', 'waktu_solat', 'hubungi', 'pengumuman', 'infaq', 'soalan_lazim', 'kuliah_mingguan']);

    foreach ([
        'step_0' => ['tier' => 'surau_ringkas'],
        'step_1' => ['official_name' => 'Surau X', 'address_line1' => 'Jln 1', 'postcode' => '53300', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01', 'authority' => 'MAIWP', 'gps' => '3.19, 101.73', 'phone_primary' => '0341491818', 'email' => 'x@test.my', 'logo_status' => $logoStatus],
        'step_2' => ['mood' => 'tenang_khusyuk'],
        'step_4' => ['panels' => ['hubungi' => ['form_recipient_email' => 'x@test.my'], 'infaq' => ['bank_name' => 'MB', 'bank_account' => '1234567890', 'account_holder' => 'Surau X']]],
        'step_5' => ['cms_updater' => 'urus_azan', 'payment_gateway' => 'manual_bank'],
        'step_6' => ['hero_mode' => $heroMode],
        'step_8' => ['domain_status' => 'belum'],
        'step_9' => ['pic_name' => 'Ali', 'pic_position' => 'SU', 'pic_phone' => '0123456789', 'consent_pdpa' => true, 'declare_truth_authority' => true],
    ] as $key => $data) {
        ProjectSection::create(['project_id' => $project->id, 'section_key' => $key, 'data' => $data]);
    }

    return [$project, $token];
}

beforeEach(function () {
    Mail::fake();
    Queue::fake();
});

it('auto-queues the first draft on submit when logo/hero are ready', function () {
    [$project, $token] = fillCompleteFor();

    $this->post("/b/{$token}/hantar")->assertSessionHas('success');

    expect($project->fresh()->status)->toBe(ProjectStatus::Submitted);
    Queue::assertPushed(GenerateDraftJob::class);
    // W1 — deep-link WA: job membawa URL asas PIC untuk pautan draf sebenar.
    Queue::assertPushed(GenerateDraftJob::class, fn ($job) => $job->picBaseUrl === url("/b/{$token}"));
});

it('submits with a friendly warning (no job) when the logo is required but not uploaded', function () {
    [$project, $token] = fillCompleteFor(logoStatus: 'ada');   // 'ada' tanpa fail → canGenerate gagal

    $this->post("/b/{$token}/hantar")->assertSessionHas('warning');

    expect($project->fresh()->status)->toBe(ProjectStatus::Submitted);   // borang tetap dihantar
    Queue::assertNotPushed(GenerateDraftJob::class);
});
