<?php

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Jobs\GenerateDraftJob;
use App\Mail\SubmittedMail;
use App\Models\ProjectSection;
use App\Services\CompletenessService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

// Fasa 6 — CompletenessService §6.12, gate Hantar, mask akaun bank §11.1.

/** Isi projek surau LENGKAP (semua medan wajib). */
function fillCompleteSurau(): array
{
    [$project, $token] = picSession(['tier' => Tier::SurauRingkas]);

    // Preset surau_ringkas.
    enablePages($project, ['utama', 'waktu_solat', 'hubungi', 'pengumuman', 'infaq', 'soalan_lazim', 'kuliah_mingguan']);

    $sections = [
        'step_0' => ['tier' => 'surau_ringkas'],
        'step_1' => [
            'official_name' => 'Surau Ujian', 'address_line1' => 'Jalan 1', 'postcode' => '53300',
            'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01', 'authority' => 'MAIWP',
            'gps' => '3.19, 101.73', 'phone_primary' => '0341491818', 'email' => 'surau@test.my', 'logo_status' => 'teks_sahaja',
        ],
        'step_2' => ['mood' => 'tenang_khusyuk'],
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

    return [$project, $token];
}

it('computes completeness score correctly — full surau = 100% (§6.12)', function () {
    [$project] = fillCompleteSurau();

    $result = app(CompletenessService::class)->compute($project);
    expect($result['score'])->toBe(100);
    expect($result['missing'])->toBeEmpty();
});

it('drops the score and lists the missing field when one required field is removed (§6.12)', function () {
    [$project] = fillCompleteSurau();

    // Buang tier (L0).
    ProjectSection::where('project_id', $project->id)->where('section_key', 'step_0')->update(['data' => []]);

    $result = app(CompletenessService::class)->compute($project);
    expect($result['score'])->toBeLessThan(100);
    expect(collect($result['missing'])->pluck('key'))->toContain('tier');
});

it('blocks submit below 100% (§6.12)', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);

    $this->post("/b/{$token}/hantar")
        ->assertRedirect(route('pic.semak', ['token' => $token]))
        ->assertSessionHas('error');

    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);
});

it('submits, notifies admin, and auto-queues the first draft when complete (§4.4/§13/Fasa 11)', function () {
    Mail::fake();
    Queue::fake();
    [$project, $token] = fillCompleteSurau();
    $project->update(['status' => ProjectStatus::InProgress]);

    $this->post("/b/{$token}/hantar")
        ->assertRedirect(route('pic.semak', ['token' => $token]))
        ->assertSessionHas('success');

    expect($project->fresh()->status)->toBe(ProjectStatus::Submitted);
    expect($project->fresh()->submitted_at)->not->toBeNull();
    Mail::assertQueued(SubmittedMail::class);
    Queue::assertPushed(GenerateDraftJob::class);   // draf dijana automatik
});

it('masks the bank account on the review page (§11.1)', function () {
    [$project, $token] = fillCompleteSurau();

    $response = $this->get("/b/{$token}/semak");
    $response->assertSee('••••7890');
    $response->assertDontSee('1234567890');
});
