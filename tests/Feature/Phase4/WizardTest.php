<?php

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Livewire\Wizard\WizardStep;
use App\Models\ProjectPage;
use App\Models\ProjectSection;
use App\Support\ZoneLookup;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\JakimZoneSeeder;
use Livewire\Livewire;

// Fasa 4 — enjin wizard + L0–L2 (§6, §6.11, §6.13, §7.5).
// (helper picSession() ditakrif dalam tests/Pest.php)

it('autosaves step data into project_sections (§6.13)', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 1])
        ->set('data.official_name', 'Masjid Autosave Test');

    $section = ProjectSection::where('project_id', $project->id)->where('section_key', 'step_1')->first();
    expect($section)->not->toBeNull();
    expect($section->data['official_name'])->toBe('Masjid Autosave Test');
});

it('applies the tier preset only once — never overwrites a touched step 3 (§6.11)', function () {
    [$project, $token] = picSession(['tier' => Tier::MasjidKariah]);

    // L3 telah disentuh secara manual + project_pages manual.
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_3', 'data' => ['pages' => ['utama']]]);
    ProjectPage::create(['project_id' => $project->id, 'page_key' => 'utama', 'enabled' => true, 'sort' => 0]);

    // Tukar tier → preset TIDAK boleh menulis ganti (step_3 disentuh).
    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 0])
        ->set('data.tier', 'masjid_besar');

    // 'sejarah' (yang preset akan hidupkan) TIDAK dicipta.
    expect(ProjectPage::where('project_id', $project->id)->where('page_key', 'sejarah')->exists())->toBeFalse();
    expect(ProjectPage::where('project_id', $project->id)->count())->toBe(1);
});

it('applies the tier preset when step 3 is untouched (§6.11)', function () {
    [$project, $token] = picSession(['tier' => Tier::SurauRingkas]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 0])
        ->set('data.tier', 'masjid_kariah');

    // Preset kariah menghidupkan 'sejarah', 'ajk', dll.
    expect(ProjectPage::where('project_id', $project->id)->where('page_key', 'sejarah')->where('enabled', true)->exists())->toBeTrue();
    expect($project->fresh()->tier)->toBe(Tier::MasjidKariah);
});

it('enforces L1 validation rules softly (postcode, phone, GPS) (§6 L1)', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 1])
        ->set('data.postcode', '123')          // bukan 5 angka
        ->set('data.phone_primary', 'abc')      // tidak sah
        ->set('data.gps', '99.9, 200')          // di luar julat Malaysia
        ->assertHasErrors(['data.postcode', 'data.phone_primary', 'data.gps']);

    // Validasi LEMBUT — data tetap disimpan walau ada ralat.
    expect(ProjectSection::where('project_id', $project->id)->where('section_key', 'step_1')->exists())->toBeTrue();
});

it('filters zone options by state (§6 L1)', function () {
    $this->seed(JakimZoneSeeder::class);

    $selangor = array_keys(ZoneLookup::forState('Selangor'));
    expect($selangor)->toEqual(['SGR01', 'SGR02', 'SGR03']);

    $johor = array_keys(ZoneLookup::forState('Johor'));
    expect($johor)->toEqual(['JHR01', 'JHR02', 'JHR03', 'JHR04']);

    // WP Labuan → WLY02 (peta negeri→zon).
    expect(array_keys(ZoneLookup::forState('W.P. Labuan')))->toContain('WLY02');
});

it('saves the design selection to project_design (§6 L2)', function () {
    $this->seed(DesignPackageSeeder::class);
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->set('data.design_package', 'biru_nilam')
        ->set('data.mood', 'mesra_keluarga')
        ->set('data.font_pair', 'B');

    $design = $project->fresh()->design;
    expect($design)->not->toBeNull();
    expect($design->package_key)->toBe('biru_nilam');
    expect($design->overrides['mood'])->toBe('mesra_keluarga');
    expect($design->overrides['font_pair'])->toBe('B');
});

it('marks the project in_progress when the wizard is first opened (§4.2)', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::Invited]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 0]);

    expect($project->fresh()->status)->toBe(ProjectStatus::InProgress);
});

it('renders the full wizard page over HTTP (smoke)', function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(JakimZoneSeeder::class);
    [$project, $token] = picSession();

    $this->get("/b/{$token}/langkah/0")->assertOk()->assertSee('Jenis Organisasi');
    $this->get("/b/{$token}/langkah/2")->assertOk()->assertSee('Pakej reka bentuk');
});
