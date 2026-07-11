<?php

use App\Enums\GenerationType;
use App\Enums\ProjectStatus;
use App\Exceptions\GateException;
use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\DraftGenerationService;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;

// §Fasa 16 W1/W6 — saluran 'template' dalam tetapan + guard penjanaan AI.

it('pipelineMode returns template when the setting is template', function () {
    Setting::put('draft_pipeline', 'template');
    expect(DraftGenerationService::pipelineMode())->toBe('template');
});

it('falls back to shell for an invalid pipeline value', function () {
    Setting::put('draft_pipeline', 'entah-apa');
    expect(DraftGenerationService::pipelineMode())->toBe('shell');
});

it('saves the template pipeline via the settings page', function () {
    $this->seed(SettingsSeeder::class);
    $this->actingAs(User::factory()->create());

    Livewire::test(ManageSettings::class)
        ->fillForm(['draft_pipeline' => 'template'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('draft_pipeline'))->toBe('template');
});

it('blocks AI draft generation entirely in template mode', function () {
    Setting::put('draft_pipeline', 'template');
    [$project] = picSession(['status' => ProjectStatus::Submitted]);

    expect(fn () => app(DraftGenerationService::class)->request($project, GenerationType::Initial, 'pic'))
        ->toThrow(GateException::class);
});
