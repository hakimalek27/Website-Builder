<?php

use App\Enums\Tier;
use App\Livewire\Wizard\WizardStep;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Services\DraftRenderer;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// Fasa 12 W6 — shell render seksyen baharu (verbatim AJK/bank/hubungi + AI visi/perutusan/faq + hero imej).

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
});

function shellProject(Tier $tier = Tier::MasjidKariah): Project
{
    [$project] = picSession(['tier' => $tier]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'phone_primary' => '03-1234 5678', 'email' => 'admin@masjid.test',
        'address_line1' => 'Jalan Masjid', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur',
        'facebook_url' => 'https://fb.com/masjid',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'perutusan' => ['role' => 'Nazir', 'name' => 'Ustaz Ahmad Farid'],
        'ajk' => ['members' => [['name' => 'En. Ali', 'position' => 'Pengerusi'], ['name' => 'En. Abu', 'position' => 'Setiausaha']]],
        'infaq' => ['bank_name' => 'Bank Islam', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian'],
    ]]]);

    return $project->fresh();
}

function shellContent(): array
{
    return validContent() + [
        'visi_misi' => ['visi' => 'Menjadi masjid contoh.', 'misi' => 'Program ibadah.', 'moto' => 'Makmur Bersama'],
        'perutusan' => ['heading' => 'Perutusan Nazir', 'quote' => 'Selamat datang ke laman rasmi kami.'],
        'faq' => [['q' => 'Bila waktu pejabat?', 'a' => '9 pagi hingga 5 petang.']],
        'infaq' => ['heading' => 'Infaq', 'paragraph' => 'Sumbangan anda membantu.'],
    ];
}

it('renders verbatim AJK, bank & contact plus AI visi/perutusan/faq', function () {
    $html = app(DraftRenderer::class)->render(shellProject(), shellContent(), 1);

    expect($html)
        ->toContain('Ustaz Ahmad Farid')        // perutusan nama verbatim
        ->toContain('Perutusan Nazir')           // heading AI
        ->toContain('Selamat datang ke laman')   // quote AI
        ->toContain('Menjadi masjid contoh')     // visi
        ->toContain('En. Ali')                   // ahli AJK
        ->toContain('1234567890')                // no. akaun bank verbatim
        ->toContain('Bila waktu pejabat')        // FAQ
        ->toContain('admin@masjid.test');        // jalur hubungi
});

it('caps the AJK grid at 12 with a "senarai penuh" note', function () {
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    $members = [];
    foreach (range(1, 20) as $i) {
        $members[] = ['name' => "Ahli {$i}", 'position' => 'AJK'];
    }
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => ['ajk' => ['members' => $members]]]]);

    $html = app(DraftRenderer::class)->render($project->fresh(), validContent(), 1);

    expect($html)->toContain('Ahli 12')
        ->not->toContain('Ahli 13')
        ->toContain('20 ahli');
});

it('embeds an uploaded hero image as a data-URI', function () {
    Storage::fake('local');
    [$project, $token] = picSession();
    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 6])
        ->set('data.hero_mode', 'upload')
        ->set('files.hero', [UploadedFile::fake()->image('hero.jpg', 1600, 900)]);

    $html = app(DraftRenderer::class)->render($project->fresh(), validContent(), 1);

    expect($html)->toContain('data:image/');
});

it('keeps NGO drafts free of the static prayer card', function () {
    $html = app(DraftRenderer::class)->render(shellProject(Tier::NgoKomuniti), validContent(), 1);

    expect($html)->not->toContain('JAKIM e-Solat');
});
