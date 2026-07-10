<?php

use App\Enums\ProjectStatus;
use App\Livewire\Wizard\WizardStep;
use App\Models\ProjectSection;
use Database\Seeders\DesignPackageSeeder;
use Livewire\Livewire;

// §Fasa 13 W7 — pratonton header/footer/pembatas + fix arabic_font.

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

it('saves arabic_font, header, footer and divider into the design overrides', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->set('data.design_package', 'warisan_hijau')
        ->set('data.mood', 'tenang_khusyuk')
        ->set('data.arabic_font', 'Scheherazade New')
        ->set('data.header_style', 'gradien')
        ->set('data.footer_style', 'tiga-lajur')
        ->set('data.divider', 'garis-emas')
        ->set('data.animations', 'zoom')
        ->call('save');

    $overrides = $project->fresh()->design->overrides;
    expect($overrides['arabic_font'])->toBe('Scheherazade New');   // dulu jatuh (bug W7)
    expect($overrides['header_style'])->toBe('gradien');
    expect($overrides['footer_style'])->toBe('tiga-lajur');
    expect($overrides['divider'])->toBe('garis-emas');
    expect($overrides['animations'])->toBe('zoom');                // §Fasa 14 varian animasi
});

it('rejects an animation value outside the allowlist (§Fasa 14)', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->set('data.design_package', 'warisan_hijau')
        ->set('data.mood', 'tenang_khusyuk')
        ->set('data.animations', 'melompat')
        ->call('save')
        ->assertHasErrors('data.animations');
});

it('normalizes a legacy boolean animation into the fade variant on mount (§Fasa 14)', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => [
        'design_package' => 'warisan_hijau', 'mood' => 'tenang_khusyuk', 'animations' => true,
    ]]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertSet('data.animations', 'fade')
        ->assertSee('data-animation="fade"', false);
});

it('renders header, footer and divider variants in the live preview', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => [
        'design_package' => 'warisan_hijau', 'mood' => 'tenang_khusyuk',
        'header_style' => 'gradien', 'footer_style' => 'tiga-lajur', 'divider' => 'garis-emas',
        'animations' => 'zoom',
    ]]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertSee('data-header="gradien"', false)
        ->assertSee('data-footer="tiga-lajur"', false)
        ->assertSee('data-divider="garis-emas"', false)
        ->assertSee('data-animation="zoom"', false);
});
