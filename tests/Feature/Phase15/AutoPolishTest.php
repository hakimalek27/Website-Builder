<?php

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Models\Setting;
use App\Services\DraftGenerationService;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

// §Fasa 15 W5 — auto-polish 1× bila QA bawah piawai.

beforeEach(function () {
    Mail::fake();
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
    Setting::put('draft_pipeline', 'html');
    Setting::put('qa_auto_polish', '1');
    htmlProviders();
});

// Draf awal LEMAH (tiada [[HERO_IMAGE]] walau stok_sementara) → polishable → cetus polish.
function weakDraft(): string
{
    return '<!DOCTYPE html><html lang="ms"><head><title>Masjid Ujian</title></head><body>'
        .'<section id="hero">Hi</section><section id="hubungi">[[CONTACT_STRIP]]</section></body></html>';
}

// Draf naik taraf: imej hero hadir → QA lulus.
function polishedDraft(): string
{
    return '<!DOCTYPE html><html lang="ms"><head><title>Masjid Ujian</title></head><body>'
        .'<section id="hero"><img src="[[HERO_IMAGE]]">Hi</section><section id="hubungi">[[CONTACT_STRIP]]</section></body></html>';
}

it('triggers one polish round, applies it, and reduces issues', function () {
    fakeTwoStagePolish(weakDraft(), polishedDraft());
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Succeeded);

    $polish = $gen->input_snapshot['stage3_polish'] ?? null;
    expect($polish)->not->toBeNull();
    expect($polish['triggered'])->toBeTrue();
    expect($polish['applied'])->toBeTrue();
    expect($polish['issues_after'])->toBeLessThan($polish['issues_before']);
    expect($gen->input_snapshot['qa']['passed'])->toBeTrue();   // QA akhir lulus
});

it('accumulates polish tokens/cost but does NOT touch the PIC quota', function () {
    fakeTwoStagePolish(weakDraft(), polishedDraft());
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    // Dua panggilan P2 (stage2 + polish) → tokens_out > satu peringkat.
    expect($gen->tokens_out)->toBeGreaterThan(20000);
    expect($gen->input_snapshot['stage3_polish']['tokens_out'])->toBe(20000);
    // Kuota AI = 1 sahaja (polish TIDAK menambah kuota).
    expect($project->fresh()->quota_ai_used)->toBe(1);
});

it('polishes over the RAW tokened html (PII-safe) with three glm calls total', function () {
    fakeTwoStagePolish(weakDraft(), polishedDraft());
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $glm = collect(Http::recorded())->filter(fn ($p) => str_contains($p[0]->url(), 'glm.test'))->values();
    expect($glm)->toHaveCount(2);   // stage2 + polish

    $polishBody = $glm[1][0]->body();
    expect($polishBody)->toContain('NAIK TARAF')->toContain('[[CONTACT_STRIP]]');
    expect($polishBody)->not->toContain('0341491818');   // telefon PIC TIDAK bocor (raw bertoken)
});

it('does not polish when qa_auto_polish is off', function () {
    Setting::put('qa_auto_polish', '0');
    fakeTwoStagePolish(weakDraft(), polishedDraft());
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Succeeded);
    expect($gen->input_snapshot['stage3_polish'] ?? null)->toBeNull();
    $glm = collect(Http::recorded())->filter(fn ($p) => str_contains($p[0]->url(), 'glm.test'));
    expect($glm)->toHaveCount(1);   // stage2 sahaja
});

it('keeps the original draft when the polish output is invalid', function () {
    // Polish pulang HTML mengandungi aksara Arab → validator tolak → draf asal kekal.
    fakeTwoStagePolish(weakDraft(), '<!DOCTYPE html><html><body><p>إنما</p></body></html>');
    [$project] = htmlReadyProject();

    app(DraftGenerationService::class)->request($project, GenerationType::Initial);

    $gen = $project->fresh()->generations()->first();
    expect($gen->status)->toBe(GenerationStatus::Succeeded);         // generation tidak gagal
    expect($gen->input_snapshot['stage3_polish']['applied'])->toBeFalse();
});
