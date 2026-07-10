<?php

use App\Enums\AiVendor;
use App\Enums\ProjectStatus;
use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\AiProviders\Pages\CreateAiProvider;
use App\Filament\Resources\AiProviders\Pages\EditAiProvider;
use App\Filament\Resources\AiProviders\Pages\ListAiProviders;
use App\Filament\Resources\Invitations\Pages\ListInvitations;
use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Widgets\FunnelStats;
use App\Models\AiProvider;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;

// §Fasa 14 W6 — audit admin: boot setiap permukaan panel tanpa ralat (tangkap 500).

beforeEach(function () {
    $this->seed(SettingsSeeder::class);
    $this->actingAs(User::factory()->create());
});

it('boots the dashboard funnel widget', function () {
    Livewire::test(FunnelStats::class)->assertOk();
});

it('boots every resource list page', function () {
    Lead::factory()->create();
    Project::factory()->create(['status' => ProjectStatus::Submitted]);
    AiProvider::factory()->create();

    Livewire::test(ListLeads::class)->assertOk();
    Livewire::test(ListProjects::class)->assertOk();
    Livewire::test(ListInvitations::class)->assertOk();
    Livewire::test(ListAiProviders::class)->assertOk();
});

it('boots the settings page with its four sections', function () {
    Livewire::test(ManageSettings::class)
        ->assertOk()
        ->assertSee('Gateway WhatsApp')
        ->assertSee('Saluran Draf');
});

it('boots the lead create and edit forms', function () {
    $lead = Lead::factory()->create();

    Livewire::test(CreateLead::class)->assertOk();
    Livewire::test(EditLead::class, ['record' => $lead->id])->assertOk();
});

it('boots the ai provider create and edit forms', function () {
    $provider = AiProvider::factory()->create();

    Livewire::test(CreateAiProvider::class)->assertOk();
    Livewire::test(EditAiProvider::class, ['record' => $provider->id])->assertOk();
});

it('saves an ai provider edit toggling prompt-engineer (regresi bug is_prompt_engineer)', function () {
    // Bug asal dilaporkan: "no such column: is_prompt_engineer" semasa simpan — migration pending.
    $provider = AiProvider::factory()->create([
        'vendor' => AiVendor::OpenAi, 'is_prompt_engineer' => false, 'is_default' => true,
    ]);

    Livewire::test(EditAiProvider::class, ['record' => $provider->id])
        ->fillForm(['is_prompt_engineer' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($provider->fresh()->is_prompt_engineer)->toBeTrue();
});

it('boots the project view and edit (action) pages', function () {
    $project = Project::factory()->create(['status' => ProjectStatus::Submitted]);

    Livewire::test(ViewProject::class, ['record' => $project->id])->assertOk();
    Livewire::test(EditProject::class, ['record' => $project->id])->assertOk();
});

it('hides project creation — projects come only from lead qualification', function () {
    expect(ProjectResource::canCreate())->toBeFalse();
    // Senarai projek tiada butang "Cipta" (§Fasa 14 W6 — buang CreateAction mengelirukan).
    expect((new ListProjects)->getCachedHeaderActions())->toBe([]);
});
