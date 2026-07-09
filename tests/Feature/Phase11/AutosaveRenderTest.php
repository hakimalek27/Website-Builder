<?php

use App\Livewire\Wizard\WizardStep;
use App\Models\ProjectSection;
use Livewire\Livewire;

// Fasa 11 — autosave skipRender: morph penuh tidak lagi menutup dropdown L4 terbuka.

function needsRender(string $token, int $step, string $name): bool
{
    $instance = Livewire::test(WizardStep::class, ['token' => $token, 'step' => $step])->instance();
    $method = new ReflectionMethod($instance, 'needsRender');
    $method->setAccessible(true);

    return $method->invoke($instance, $name);
}

it('skips render for scalar text autosave but renders reactive fields (step 1)', function () {
    [$project, $token] = picSession();

    expect(needsRender($token, 1, 'data.official_name'))->toBeFalse()
        ->and(needsRender($token, 1, 'data.postcode'))->toBeFalse()
        ->and(needsRender($token, 1, 'data.state'))->toBeTrue()          // kawal opsyen zon
        ->and(needsRender($token, 1, 'data.logo_status'))->toBeTrue();   // dedah upload logo
});

it('always renders step 2 for the live design preview', function () {
    [$project, $token] = picSession();

    expect(needsRender($token, 2, 'data.font_pair'))->toBeTrue()
        ->and(needsRender($token, 2, 'data.mood'))->toBeTrue();
});

it('renders only radio/checkbox drivers in step 4 — not text or select (the dropdown bug)', function () {
    [$project, $token] = picSession();

    expect(needsRender($token, 4, 'data.panels.sejarah.mode'))->toBeTrue()            // radio
        ->and(needsRender($token, 4, 'data.panels.ajk.full_list_later'))->toBeTrue()  // checkbox
        ->and(needsRender($token, 4, 'data.panels.perutusan.name'))->toBeFalse()      // text
        ->and(needsRender($token, 4, 'data.panels.perutusan.role'))->toBeFalse();     // select ← punca bug
});

it('dispatches wizard-saved and still persists on scalar autosave', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 1])
        ->set('data.official_name', 'Masjid SkipRender')
        ->assertDispatched('wizard-saved');

    expect(ProjectSection::where('project_id', $project->id)->where('section_key', 'step_1')->first()->data['official_name'])
        ->toBe('Masjid SkipRender');
});
