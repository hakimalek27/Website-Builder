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

// Regression: step-3/step-4 blade dulu guna PageCatalog::meta()/panels()/clusters() (masjid sahaja)
// → render NGO 500 "Undefined array key derma". Kini render() pass versi *For($tier).
it('renders the NGO step 3 clusters and step 4 panels via HTTP', function () {
    [$project, $token] = picSession(['tier' => Tier::NgoKomuniti]);
    enablePages($project, ['utama', 'profil', 'program_utama', 'sukarelawan', 'keahlian', 'derma', 'hubungi']);

    $ngoCluster = array_key_first(PageCatalog::clustersFor(Tier::NgoKomuniti));
    $dermaLabel = PageCatalog::metaFor(Tier::NgoKomuniti)['derma']['label'];

    $this->get("/b/{$token}/langkah/3")->assertOk()->assertSee($ngoCluster);
    $this->get("/b/{$token}/langkah/4")->assertOk()->assertSee($dermaLabel);
});

it('still renders masjid step 3 and step 4 via HTTP (byte-path guard)', function () {
    [$project, $token] = picSession(['tier' => Tier::MasjidKariah]);
    enablePages($project, ['utama', 'hubungi', 'infaq']);

    $this->get("/b/{$token}/langkah/3")->assertOk();
    $this->get("/b/{$token}/langkah/4")->assertOk();
});
