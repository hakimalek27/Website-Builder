<?php

use App\Enums\Tier;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\Ai\DraftContentValidator;
use App\Services\Ai\PromptBuilder;
use App\Services\DraftRenderer;
use App\Services\SpecBuilder;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;

// Fasa 11 — penjanaan draf NGO (prompt/validator/shell/spec).

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
});

function ngoContent(): array
{
    return [
        'meta' => ['title' => 'Yayasan Amal', 'description' => 'Laman rasmi Yayasan Amal.'],
        'hero' => ['eyebrow' => 'Selamat Datang', 'headline' => 'Yayasan Amal', 'subheadline' => 'Bersama membina komuniti.', 'cta_primary_label' => 'Derma', 'cta_secondary_label' => 'Sertai'],
        'about' => ['heading' => 'Tentang Kami', 'paragraphs' => ['Kami sebuah pertubuhan kebajikan.'], 'stats' => [['label' => 'Ditubuhkan', 'value' => '2015']]],
        'programs' => [['title' => 'Program Yatim', 'blurb' => 'Bantuan untuk anak yatim setempat.']],
        'volunteer' => ['heading' => 'Jadi Sukarelawan', 'paragraph' => 'Sertai gerakan kami.', 'cta_label' => 'Daftar'],
        'donate' => ['heading' => 'Derma', 'paragraph' => 'Sumbangan anda membantu.'],
        'footer_description' => 'Yayasan Amal — bersama komuniti.',
    ];
}

function ngoProject(): Project
{
    [$project] = picSession(['tier' => Tier::NgoKomuniti]);
    enablePages($project, ['utama', 'profil', 'program_utama', 'sukarelawan', 'derma', 'hubungi']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['design_package' => 'harapan_hijau', 'mood' => 'bersemangat_muda']]);

    return $project->fresh();
}

it('builds an NGO prompt with NGO keys and no mosque services', function () {
    $prompt = app(PromptBuilder::class)->build(ngoProject());

    expect($prompt['requested_keys'])->toContain('programs', 'volunteer', 'donate')
        ->and($prompt['requested_keys'])->not->toContain('services')
        ->and($prompt['service_keys'])->toBe([])
        ->and($prompt['system'])->toContain('pertubuhan/NGO');
});

it('validates NGO content keys (programs/volunteer/donate)', function () {
    $project = ngoProject();
    $prompt = app(PromptBuilder::class)->build($project);

    $validated = app(DraftContentValidator::class)->validate(
        json_encode(ngoContent(), JSON_UNESCAPED_UNICODE),
        $prompt['requested_keys'],
        $prompt['service_keys'],
    );

    expect($validated)->toHaveKeys(['programs', 'volunteer', 'donate']);
});

it('renders an NGO draft with Program + Derma sections and no prayer card', function () {
    $html = app(DraftRenderer::class)->render(ngoProject(), ngoContent(), 1);

    expect($html)->toContain('Program')
        ->toContain('Derma Sekarang')
        ->toContain('Jadi Sukarelawan')
        ->toContain('DRAF SAMPEL')
        ->not->toContain('waktu sebenar akan diambil');   // NGO tiada blok waktu solat
});

it('produces an NGO spec content block (no mosque services)', function () {
    $project = ngoProject();
    $project->design()->create(['package_key' => 'harapan_hijau', 'overrides' => []]);

    $spec = app(SpecBuilder::class)->build($project->fresh());

    expect($spec['content'])->toHaveKeys(['profil', 'programs', 'volunteer', 'membership', 'derma'])
        ->and($spec['content'])->not->toHaveKey('services')
        ->and($spec['features']['prayer'])->toBeNull()
        ->and($spec['meta']['tier'])->toBe('ngo_komuniti');
});
