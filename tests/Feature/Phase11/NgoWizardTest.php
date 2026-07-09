<?php

use App\Enums\Tier;
use App\Livewire\Wizard\WizardStep;
use App\Models\Lead;
use App\Models\ProjectPage;
use App\Services\CompletenessService;
use App\Services\LeadQualifier;
use App\Support\PageCatalog;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

// Fasa 11 — sokongan NGO / pertubuhan (wizard).

it('applies the NGO preset pages when an NGO tier is chosen', function () {
    [$project, $token] = picSession(['tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 0])
        ->set('data.tier', 'ngo_komuniti');

    expect($project->fresh()->tier)->toBe(Tier::NgoKomuniti)
        ->and(ProjectPage::where('project_id', $project->id)->where('page_key', 'derma')->where('enabled', true)->exists())->toBeTrue()
        ->and(ProjectPage::where('project_id', $project->id)->where('page_key', 'sukarelawan')->where('enabled', true)->exists())->toBeTrue()
        ->and(ProjectPage::where('project_id', $project->id)->where('page_key', 'waktu_solat')->exists())->toBeFalse();
});

it('forces is_gov false for NGO tiers', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 0])
        ->set('data.is_gov', true)
        ->set('data.tier', 'ngo_penuh');

    expect($project->fresh()->is_gov)->toBeFalse();
});

it('uses ROS-style NGO role options in the perutusan panel', function () {
    $roles = collect(PageCatalog::panelsFor(Tier::NgoPenuh)['perutusan'])
        ->firstWhere('key', 'role')['options'];

    expect($roles)->toHaveKeys(['Penaung', 'Pengerusi', 'Setiausaha', 'Bendahari', 'Pengarah Eksekutif'])
        ->and($roles)->not->toHaveKey('Nazir');
});

it('does not require the JAKIM zone for NGO completeness', function () {
    [$project] = picSession(['tier' => Tier::NgoKomuniti]);

    $missing = collect(app(CompletenessService::class)->compute($project)['missing'])->pluck('key');

    expect($missing)->not->toContain('jakim_zone');
});

it('qualifies an NGO lead into an NGO-tier project', function () {
    Notification::fake();

    $lead = Lead::create([
        'mosque_name' => 'Yayasan Amal Test',
        'org_type' => 'ngo',
        'state' => 'Selangor',
        'pic_name' => 'Ali',
        'pic_phone' => '0123456789',
    ]);

    $result = app(LeadQualifier::class)->qualify($lead, 'ali@test.my');

    expect($result['project']->tier)->toBe(Tier::NgoKomuniti);
});

it('shows the NGO group and cards on step 0', function () {
    [$project, $token] = picSession();

    $this->get("/b/{$token}/langkah/0")->assertOk()
        ->assertSee('Pertubuhan / NGO')
        ->assertSee('NGO (Komuniti)');
});
