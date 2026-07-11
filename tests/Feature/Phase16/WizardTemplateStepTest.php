<?php

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Livewire\Wizard\WizardStep;
use App\Models\Setting;
use App\Models\TemplateCatalog;
use Livewire\Livewire;

// §Fasa 16 W2 — wizard L2 mod templat (galeri rujukan).

it('renders the template gallery when pipeline is template', function () {
    Setting::put('draft_pipeline', 'template');
    TemplateCatalog::factory()->create(['name' => 'Masjid Moden X', 'categories' => ['masjid']]);
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertSet('templateMode', true)
        ->assertSee('Masjid Moden X')
        ->assertSee('Nota reka bentuk anda')
        ->assertSee('Nada penulisan')
        ->assertDontSee('Pakej reka bentuk');
});

it('renders the classic design step when pipeline is not template', function () {
    Setting::put('draft_pipeline', 'shell');
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertSet('templateMode', false)
        ->assertSee('Pakej reka bentuk');
});

it('selects a template and stores a snapshot', function () {
    Setting::put('draft_pipeline', 'template');
    $tpl = TemplateCatalog::factory()->create([
        'name' => 'Amanah Biru', 'url' => 'https://x.test/t', 'demo_url' => 'https://x.test/demo',
    ]);
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->call('selectTemplate', $tpl->id)
        ->assertSet('templateMode', true);

    $data = $project->sections()->where('section_key', 'step_2')->first()->data;
    expect($data['template_id'])->toBe($tpl->id);
    expect($data['template_snapshot']['name'])->toBe('Amanah Biru');
    expect($data['template_snapshot']['url'])->toBe('https://x.test/t');
    expect($data['template_snapshot']['demo_url'])->toBe('https://x.test/demo');
});

it('filters templates by search term', function () {
    Setting::put('draft_pipeline', 'template');
    TemplateCatalog::factory()->create(['name' => 'Hijau Tenang', 'categories' => ['masjid'], 'style_tags' => ['tenang']]);
    TemplateCatalog::factory()->create(['name' => 'Biru Korporat', 'categories' => ['masjid'], 'style_tags' => ['korporat']]);
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->set('templateSearch', 'korporat')
        ->assertSee('Biru Korporat')
        ->assertDontSee('Hijau Tenang');
});

it('shows only templates matching the project tier', function () {
    Setting::put('draft_pipeline', 'template');
    TemplateCatalog::factory()->create(['name' => 'Masjid Sahaja', 'categories' => ['masjid']]);
    TemplateCatalog::factory()->create(['name' => 'Pertubuhan Sahaja', 'categories' => ['ngo']]);
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::NgoKomuniti]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->assertSee('Pertubuhan Sahaja')
        ->assertDontSee('Masjid Sahaja');
});

it('accepts a custom url and structured notes (soft validation, still saves)', function () {
    Setting::put('draft_pipeline', 'template');
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->set('data.template_custom_url', 'https://laman-saya.test')
        ->set('data.template_notes.suka', 'Warna tenang, kemas')
        ->set('data.template_notes.ubah', 'Buang slider besar')
        ->set('data.mood', 'tenang_khusyuk');

    $data = $project->sections()->where('section_key', 'step_2')->first()->data;
    expect($data['template_custom_url'])->toBe('https://laman-saya.test');
    expect($data['template_notes']['suka'])->toBe('Warna tenang, kemas');
    expect($data['template_notes']['ubah'])->toBe('Buang slider besar');
});

it('clears a template selection', function () {
    Setting::put('draft_pipeline', 'template');
    $tpl = TemplateCatalog::factory()->create();
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->call('selectTemplate', $tpl->id)
        ->call('clearTemplate');

    $data = $project->sections()->where('section_key', 'step_2')->first()->data;
    expect($data['template_id'] ?? null)->toBeNull();
    expect($data['template_snapshot'] ?? null)->toBeNull();
});

it('does not create a ProjectDesign row in template mode', function () {
    Setting::put('draft_pipeline', 'template');
    $tpl = TemplateCatalog::factory()->create();
    [$project, $token] = picSession(['status' => ProjectStatus::InProgress, 'tier' => Tier::MasjidKariah]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 2])
        ->call('selectTemplate', $tpl->id)
        ->set('data.mood', 'tenang_khusyuk');

    expect($project->design()->exists())->toBeFalse();
});
