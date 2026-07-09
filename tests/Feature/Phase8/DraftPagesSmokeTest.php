<?php

use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Models\Generation;
use App\Models\ProjectSection;
use App\Services\DraftRenderer;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;

// Fasa 8 — smoke: pemapar P5/P6/P7/P8/P9 dirender; P6 header CSP betul (§5.2 P6).

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
});

it('renders draft viewer pages and serves P6 raw with strict CSP + noindex', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::DraftReady]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['design_package' => 'warisan_hijau', 'mood' => 'tenang_khusyuk']]);
    enablePages($project, ['utama', 'hubungi']);

    $content = validContent();
    $gen = Generation::factory()->for($project)->succeeded()->create(['type' => GenerationType::Initial, 'output_json' => $content]);
    $path = app(DraftRenderer::class)->renderAndStore($project, $gen, $content, 1);
    $gen->update(['rendered_path' => $path]);

    // P5 pemapar.
    $this->get("/b/{$token}/draf/{$gen->id}")->assertOk()->assertSee('DRAF SAMPEL');
    // P6 mentah — header CSP + noindex.
    $raw = $this->get("/b/{$token}/draf/{$gen->id}/penuh");
    $raw->assertOk();
    $raw->assertHeader('X-Robots-Tag', 'noindex');
    expect($raw->headers->get('Content-Security-Policy'))->toContain("default-src 'none'");
    expect($raw->headers->get('Content-Security-Policy'))->toContain('fonts.googleapis.com');

    // P7/P8/P9.
    $this->get("/b/{$token}/tweak/reka")->assertOk();
    $this->get("/b/{$token}/tweak/kandungan")->assertOk();
    $this->get("/b/{$token}/lulus")->assertOk()->assertSee('Luluskan Draf');
});
