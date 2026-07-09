<?php

use App\Enums\AiDriver;
use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Exceptions\GateException;
use App\Mail\GenerationFailedMail;
use App\Models\AiProvider;
use App\Models\Generation;
use App\Models\ProjectSection;
use App\Services\Ai\AnthropicClient;
use App\Services\DraftGenerationService;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

// Fasa 7 — Modul AI & penjanaan draf (§8, §9). R12: Http::fake sentiasa.
// (helper validContent() & fakeAnthropic() ditakrif dalam tests/Pest.php)

function readyProject(array $extraPages = []): array
{
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted, 'tier' => Tier::SurauRingkas]);
    enablePages($project, array_merge(['utama', 'hubungi'], $extraPages));

    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'official_name' => 'Masjid Ujian', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'logo_status' => 'teks_sahaja',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['mood' => 'tenang_khusyuk']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_6', 'data' => ['hero_mode' => 'stok_sementara']]);

    AiProvider::factory()->default()->create(['driver' => AiDriver::Anthropic]);

    return [$project, $token];
}

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
});

it('AiClient completes a mini call via Http::fake (Uji Sambungan)', function () {
    Http::fake(['*api.anthropic.com*' => Http::response([
        'content' => [['type' => 'text', 'text' => 'OK']],
        'usage' => ['input_tokens' => 5, 'output_tokens' => 1],
    ], 200)]);

    $provider = AiProvider::factory()->create(['driver' => AiDriver::Anthropic]);
    $result = app(AnthropicClient::class)->complete('sys', 'user', $provider);

    expect($result->content)->toBe('OK');
    expect($result->tokensIn)->toBe(5);
});

it('lock prevents concurrent generation (§4.3)', function () {
    [$project] = readyProject();
    Generation::factory()->for($project)->processing()->create();

    expect(fn () => app(DraftGenerationService::class)->request($project, GenerationType::Initial))
        ->toThrow(GateException::class);
});

it('blocks generation before submit (§6.12)', function () {
    [$project] = readyProject();
    $project->update(['status' => ProjectStatus::InProgress]);

    expect(fn () => app(DraftGenerationService::class)->request($project, GenerationType::Initial))
        ->toThrow(GateException::class);
});

it('increments quota only on success (§8.7)', function () {
    fakeAnthropic(validContent());
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    expect($project->fresh()->quota_ai_used)->toBe(1);
    expect($project->fresh()->generations()->first()->status)->toBe(GenerationStatus::Succeeded);
});

it('refunds quota and mails admin on failure (§8.6)', function () {
    Mail::fake();
    fakeAnthropic([], 500); // semua percubaan gagal
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    expect($project->fresh()->quota_ai_used)->toBe(0); // TIDAK disentuh
    expect($project->fresh()->generations()->first()->status)->toBe(GenerationStatus::Failed);
    Mail::assertQueued(GenerationFailedMail::class);
});

it('enforces cooldown (§8.7)', function () {
    [$project] = readyProject();
    // Jana AI terakhir baru sahaja selesai.
    Generation::factory()->for($project)->succeeded()->create([
        'type' => GenerationType::Initial, 'finished_at' => now()->subMinute(),
    ]);

    expect(fn () => app(DraftGenerationService::class)->request($project, GenerationType::Initial))
        ->toThrow(GateException::class);
});

it('enforces the daily ceiling of 10 (§11.2)', function () {
    [$project] = readyProject();
    Generation::factory()->count(10)->for($project)->create(['status' => GenerationStatus::Failed]);

    expect(fn () => app(DraftGenerationService::class)->request($project, GenerationType::Initial))
        ->toThrow(GateException::class);
});

it('rejects output containing Arabic characters (§9.1)', function () {
    $content = validContent();
    $content['hero']['headline'] = 'Masjid السلام'; // satu perkataan Arab
    fakeAnthropic($content);
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    expect($project->fresh()->generations()->first()->status)->toBe(GenerationStatus::Failed);
    expect($project->fresh()->quota_ai_used)->toBe(0);
});

it('rejects length violations over 125% (§8.4)', function () {
    $content = validContent();
    $content['meta']['title'] = str_repeat('a', 100); // had 60 → >125%
    fakeAnthropic($content);
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    expect($project->fresh()->generations()->first()->status)->toBe(GenerationStatus::Failed);
});

it('rejects unknown keys (§8.4)', function () {
    $content = validContent();
    $content['unexpected_key'] = 'foo';
    fakeAnthropic($content);
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    expect($project->fresh()->generations()->first()->status)->toBe(GenerationStatus::Failed);
});

it('draft HTML contains the watermark and noindex (§8.5)', function () {
    fakeAnthropic(validContent());
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);
    $gen = $project->fresh()->generations()->first();
    $html = Storage::disk('local')->get($gen->rendered_path);

    expect($html)->toContain('DRAF SAMPEL — BUKAN LAMAN SEBENAR');
    expect($html)->toContain('name="robots" content="noindex"');
});

it('draft prayer block is a labeled static example (§8.5/§9.3)', function () {
    fakeAnthropic(validContent());
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);
    $gen = $project->fresh()->generations()->first();
    $html = Storage::disk('local')->get($gen->rendered_path);

    expect($html)->toContain('Contoh paparan — waktu sebenar akan diambil terus dari JAKIM e-Solat');
});

it('never sends PIC phone or bank account in the prompt (§12.7)', function () {
    fakeAnthropic(validContent());
    [$project, $token] = readyProject(['infaq']);
    $project->invitation->update(['pic_phone' => '0199999999']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => [
        'panels' => ['infaq' => ['bank_account' => '9876543210', 'bank_name' => 'Maybank', 'account_holder' => 'X']],
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_5', 'data' => ['payment_gateway' => 'manual_bank', 'cms_updater' => 'urus_azan']]);

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    Http::assertSent(function ($request) {
        $body = $request->body();

        return ! str_contains($body, '0199999999') && ! str_contains($body, '9876543210');
    });
});

it('records the cost ledger from provider rates (§8.8)', function () {
    fakeAnthropic(validContent());
    [$project] = readyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);
    $gen = $project->fresh()->generations()->first();

    // rate 3/15 per Mtok × (1200 in, 800 out) = 0.0036 + 0.012 = 0.0156
    expect((float) $gen->cost_estimate)->toBeGreaterThan(0);
});
