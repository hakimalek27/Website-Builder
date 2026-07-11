<?php

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Exceptions\GateException;
use App\Models\Setting;
use App\Services\DesignRerenderService;
use App\Services\DraftGenerationService;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

// §Fasa 13 W5 — tweak kandungan pada draf HTML (Peringkat 2 sahaja).
// Helper htmlProviders/htmlReadyProject/validHtmlBody/fakeTwoStage → tests/Pest.php.

beforeEach(function () {
    Mail::fake();
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
    Setting::put('draft_pipeline', 'html');
    Setting::put('qa_auto_polish', '0');   // §Fasa 15 — determinisme (tweak juga lalui QA/polish)
});

it('applies a content tweak on an html draft as stage-2 only, without leaking PII', function () {
    htmlProviders();
    fakeTwoStage(validHtmlBody());
    [$project, $token] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial, 'pic', null, url('/b/'.$token));
    $first = $project->fresh()->generations()->latest()->first();
    expect($first->status)->toBe(GenerationStatus::Succeeded);

    Setting::put('gen_cooldown_minutes', '0'); // elak gate cooldown

    $this->post("/b/{$token}/tweak/kandungan", ['categories' => ['warna'], 'message' => 'Tukar ke biru'])
        ->assertRedirect(route('pic.jana', ['token' => $token]));

    expect($project->fresh()->generations()->count())->toBe(2);
    $tweak = $project->fresh()->generations()->where('id', '!=', $first->id)->first();
    expect($tweak->input_snapshot['pipeline'])->toBe('html');
    expect($tweak->input_snapshot['stage1']['source'])->toBe('tweak');
    expect($project->fresh()->quota_ai_used)->toBe(2);
    $this->assertDatabaseHas('tweak_requests', ['project_id' => $project->id]);

    // GLM menerima HTML MENTAH bertoken (ada label + token, TIADA PII telefon).
    Http::assertSent(fn ($r) => str_contains($r->url(), 'glm.test')
        && str_contains($r->body(), 'HTML SEMASA')
        && str_contains($r->body(), '[[CONTACT_STRIP]]')
        && ! str_contains($r->body(), '0341491818'));
});

it('blocks the free design re-render for an html draft', function () {
    htmlProviders();
    fakeTwoStage(validHtmlBody());
    [$project] = htmlReadyProject();
    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    expect(fn () => app(DesignRerenderService::class)->rerender($project->fresh()))
        ->toThrow(GateException::class);
});
