<?php

use App\Livewire\Wizard\WizardStep;
use App\Models\ProjectPage;
use App\Models\ProjectSection;
use App\Support\PageCatalog;
use Livewire\Livewire;

// Fasa 5 — L3 struktur halaman + L4 enjin sub-borang + L5 (§6 L3–L5).

it('saves the page checklist and counts pages (§6 L3)', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 3])
        ->set('data.pages', ['sejarah', 'galeri', 'kelas_quran']);

    $enabled = ProjectPage::where('project_id', $project->id)->where('enabled', true)->pluck('page_key');
    expect($enabled)->toContain('sejarah', 'galeri', 'kelas_quran');
    // Wajib sentiasa hidup.
    expect($enabled)->toContain('utama', 'hubungi');
});

it('never disables the mandatory pages utama & hubungi (§6 L3)', function () {
    [$project, $token] = picSession();

    // Cuba kosongkan semua pilihan.
    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 3])
        ->set('data.pages', []);

    expect(ProjectPage::where('project_id', $project->id)->where('page_key', 'utama')->where('enabled', true)->exists())->toBeTrue();
    expect(ProjectPage::where('project_id', $project->id)->where('page_key', 'hubungi')->where('enabled', true)->exists())->toBeTrue();
});

it('shows L4 panels only for enabled pages and preserves data when disabled (§6 L4)', function () {
    [$project, $token] = picSession();
    enablePages($project, ['utama', 'hubungi', 'nikah']);

    $t = Livewire::test(WizardStep::class, ['token' => $token, 'step' => 4]);
    $t->assertSee('Nikah'); // panel muncul
    $t->set('data.panels.nikah.short_desc', 'Khidmat nikah kariah');

    // Nyah-tanda nikah.
    $project->pages()->where('page_key', 'nikah')->update(['enabled' => false]);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 4])
        ->assertDontSee('>Nikah<', false);

    // Data panel nikah KEKAL tersimpan.
    $section = ProjectSection::where('project_id', $project->id)->where('section_key', 'step_4')->first();
    expect($section->data['panels']['nikah']['short_desc'])->toBe('Khidmat nikah kariah');
});

it('locks the Quran class level enum (§6 L4)', function () {
    expect(PageCatalog::QURAN_LEVELS)->toBe(['tahsin', 'hafazan', 'tadabbur', 'dhuha', 'tajwid', 'ulum', 'qiraat']);

    [$project, $token] = picSession();
    enablePages($project, ['utama', 'hubungi', 'kelas_quran']);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 4])
        ->set('data.panels.kelas_quran.classes', [['name' => 'Kelas A', 'level' => 'invalid_level']])
        ->assertHasErrors('data.panels.kelas_quran.classes.0.level');
});

it('prefills four infaq categories (§6 L4)', function () {
    [$project, $token] = picSession();
    enablePages($project, ['utama', 'hubungi', 'infaq']);

    $t = Livewire::test(WizardStep::class, ['token' => $token, 'step' => 4]);

    $categories = $t->get('data')['panels']['infaq']['categories'];
    expect($categories)->toHaveCount(4);
    expect(collect($categories)->pluck('title')->all())->toBe(['Infaq Am', 'Wakaf', 'Pembinaan', 'Anak Yatim']);
});

it('requires gallery consent when files are present (§6 L4)', function () {
    [$project, $token] = picSession();
    enablePages($project, ['utama', 'hubungi', 'galeri']);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 4])
        ->set('data.panels.galeri.images', ['image-1.jpg'])
        ->set('data.panels.galeri.consent', false)
        ->assertHasErrors('data.panels.galeri.consent');
});

it('requires cms_updater on step 5 (§6 L5)', function () {
    [$project, $token] = picSession();

    $t = Livewire::test(WizardStep::class, ['token' => $token, 'step' => 5])
        ->set('data.bilingual', true); // cetuskan simpan tanpa cms_updater
    $t->assertHasErrors('data.cms_updater');

    $t->set('data.cms_updater', 'urus_azan')->assertHasNoErrors('data.cms_updater');
});

it('requires payment_gateway when infaq is enabled (§6.12)', function () {
    [$project, $token] = picSession();
    enablePages($project, ['utama', 'hubungi', 'infaq']);

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 5])
        ->set('data.cms_updater', 'ajk_sendiri')
        ->assertHasErrors('data.payment_gateway');
});
