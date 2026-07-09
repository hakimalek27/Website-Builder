<?php

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Exceptions\GateException;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Livewire\Wizard\WizardStep;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Services\DesignRerenderService;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Livewire\Livewire;

// Fasa 11 — pepijat admin + wizard.

it('ProjectStatus implements Filament HasLabel + HasColor for every case', function () {
    expect(ProjectStatus::Submitted)->toBeInstanceOf(HasLabel::class)
        ->and(ProjectStatus::Submitted)->toBeInstanceOf(HasColor::class);

    foreach (ProjectStatus::cases() as $case) {
        expect($case->getLabel())->toBe($case->label())->not->toBe('');
        expect($case->getColor())->toBeIn(['gray', 'info', 'warning', 'success', 'primary', 'danger']);
    }
});

it('Tier implements Filament HasLabel', function () {
    expect(Tier::MasjidKariah)->toBeInstanceOf(HasLabel::class);
    expect(Tier::MasjidKariah->getLabel())->toBe('Masjid Kariah');
});

it('renders /admin/projects without a 500 — enum badge no longer instantiates the container', function () {
    $this->actingAs(User::factory()->create());

    Project::factory()->create(['status' => ProjectStatus::Submitted]);
    Project::factory()->create(['status' => ProjectStatus::DraftReady]);
    Project::factory()->create(['status' => ProjectStatus::Live]);

    Livewire::test(ListProjects::class)
        ->assertOk()
        ->assertSee('Telah Dihantar');
});

it('advances from the final wizard step straight to Semak & Hantar (§next bug)', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 9])
        ->call('next')
        ->assertRedirect(route('pic.semak', ['token' => $token]));
});

it('caps design re-render using the default_design_quota setting (un-orphan)', function () {
    Setting::put('default_design_quota', '1');
    [$project] = picSession(['status' => ProjectStatus::DraftReady, 'quota_design_used' => 1]);

    expect(fn () => app(DesignRerenderService::class)->rerender($project->fresh()))
        ->toThrow(GateException::class, 'Kuota render reka bentuk (1) telah habis.');
});
