<?php

use App\Enums\ProjectStatus;
use App\Livewire\Wizard\WizardStep;
use App\Models\Asset;
use App\Models\ProjectSection;
use Database\Seeders\DesignPackageSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;

// §Fasa 15 W6 — pratonton wizard menunaikan janji (corak Islamik, logo, foto stok).

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

it('shows the islamic pattern and arabesque markers when chosen', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => [
        'design_package' => 'warisan_hijau', 'mood' => 'tenang_khusyuk',
        'islamic_elements' => ['corak_geometri' => true, 'pembatas_arabesque' => true],
    ]]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertSee('data-preview-corak', false)
        ->assertSee('data-preview-arabesque', false)
        ->assertSee('Corak Islamik');
});

it('shows the logo thumbnail in the preview when a logo is uploaded', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['design_package' => 'warisan_hijau', 'mood' => 'tenang_khusyuk']]);
    Asset::create(['project_id' => $project->id, 'kind' => 'logo', 'path' => 'a/'.Str::ulid().'.png', 'original_name' => 'l.png', 'mime' => 'image/png', 'size' => 3000, 'sort' => 0]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertSee('data-preview-logo', false);
});

it('stays backward compatible with no premium markers when nothing is chosen', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['design_package' => 'warisan_hijau', 'mood' => 'tenang_khusyuk']]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertDontSee('data-preview-corak', false)
        ->assertDontSee('data-preview-arabesque', false)
        ->assertDontSee('data-preview-logo', false)
        ->assertSee('data-animation', false);           // pratonton asal kekal berfungsi
});
