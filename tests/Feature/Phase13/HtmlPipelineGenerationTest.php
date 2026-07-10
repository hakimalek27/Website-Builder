<?php

use App\Enums\AiDriver;
use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Models\AiProvider;
use App\Models\Setting;
use App\Services\DraftGenerationService;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

// §Fasa 13 W4 — saluran HTML dua-peringkat (jurutera prompt → jana HTML).
// Helper htmlProviders/htmlReadyProject/validHtmlBody/fakeTwoStage ditakrif dalam tests/Pest.php.

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
    Setting::put('draft_pipeline', 'html');
    Setting::put('html_max_tokens', '30000');
});

it('generates an HTML draft via the two-stage pipeline', function () {
    Setting::put('whatsapp_gateway_url', 'https://gw.test');
    htmlProviders();
    fakeTwoStage(validHtmlBody());
    [$project, $token] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial, 'pic', null, url('/b/'.$token));

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Succeeded);
    expect($gen->output_json)->toBeNull();
    expect($gen->rendered_path)->not->toBeNull();
    expect($gen->input_snapshot['pipeline'])->toBe('html');
    expect($gen->input_snapshot['engineered_prompt'])->toContain('bina draf HTML');
    expect($gen->input_snapshot['stage1']['model'])->toBe('gpt-5.5');
    expect($gen->input_snapshot['stage2']['model'])->toBe('glm-5.2');
    expect($gen->tokens_in)->toBe(7000);        // 4000 + 3000
    expect($gen->tokens_out)->toBe(22000);      // 2000 + 20000
    expect(round((float) $gen->cost_estimate, 4))->toBe(0.1722); // P1 0.08 + P2 0.0922
    expect($project->fresh()->quota_ai_used)->toBe(1);
    expect($project->fresh()->status)->toBe(ProjectStatus::DraftReady);

    // Draf disimpan + verbatim + chrome DRAF disisip pelayan.
    $html = Storage::disk('local')->get($gen->rendered_path);
    expect($html)->toContain('data-reka="contact"')->toContain('— DRAF');

    // WA kepada PIC.
    Http::assertSent(fn ($r) => str_contains($r->url(), 'gw.test'));
});

it('calls stage 1 without json and stage 2 with the html token cap', function () {
    htmlProviders();
    fakeTwoStage(validHtmlBody());
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'engineer.test') && ! isset($r->data()['response_format']));
    Http::assertSent(fn ($r) => str_contains($r->url(), 'glm.test') && ($r->data()['max_tokens'] ?? null) === 30000);
});

it('never sends PII to the prompt engineer', function () {
    htmlProviders();
    fakeTwoStage(validHtmlBody());
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    Http::assertSent(fn ($r) => ! str_contains($r->url(), 'engineer.test')
        || (! str_contains($r->body(), '0341491818') && ! str_contains($r->body(), 'surau@test.my')));
});

it('fails immediately when no prompt engineer is configured', function () {
    Mail::fake();
    AiProvider::factory()->create(['driver' => AiDriver::OpenAiCompatible, 'base_url' => 'https://glm.test/v1', 'model' => 'glm-5.2', 'is_default' => true]);
    Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => 'x']]]])]);
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Failed);
    expect($gen->error)->toContain('Jurutera Prompt belum diset');
    expect($project->fresh()->quota_ai_used)->toBe(0);
});

it('fails when the prompt engineer call errors', function () {
    Mail::fake();
    htmlProviders();
    Http::fake([
        'engineer.test/*' => Http::response('', 500),
        'glm.test/*' => Http::response(['choices' => [['message' => ['content' => validHtmlBody()]]]]),
    ]);
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Failed);
    expect($gen->error)->toContain('Peringkat 1');
    expect($project->fresh()->quota_ai_used)->toBe(0);
});

it('retries only stage 2 on validation failure and records wasted cost', function () {
    Mail::fake();
    htmlProviders();
    fakeTwoStage('<html><body>tiada penutup html');   // tiada </html> → validator gagal
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Failed);
    expect($project->fresh()->quota_ai_used)->toBe(0);

    // Peringkat 1 SEKALI; peringkat 2 diulang 3× (jimat token jurutera prompt).
    expect(Http::recorded(fn ($r) => str_contains($r->url(), 'engineer.test')))->toHaveCount(1);
    expect(Http::recorded(fn ($r) => str_contains($r->url(), 'glm.test')))->toHaveCount(3);

    // Kos terbazir direkod (bukan sifar); kuota tidak dicaj.
    expect((float) $gen->cost_estimate)->toBeGreaterThan(0);
});
