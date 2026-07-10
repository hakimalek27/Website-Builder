<?php

use App\Models\ProjectSection;
use App\Services\DesignResolver;
use App\Services\DraftRenderer;
use Database\Seeders\DesignPackageSeeder;
use Database\Seeders\VerseLibrarySeeder;

// Fasa 11 — varian shell draf: pelbagaian struktur (layout/header/footer/card/divider).

beforeEach(function () {
    $this->seed(DesignPackageSeeder::class);
    $this->seed(VerseLibrarySeeder::class);
});

function renderDraftWith(array $overrides): string
{
    [$project] = picSession();
    enablePages($project, ['utama', 'hubungi']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['design_package' => 'warisan_hijau', 'mood' => 'tenang_khusyuk']]);
    $project->design()->create(['package_key' => 'warisan_hijau', 'overrides' => $overrides]);

    return app(DraftRenderer::class)->render($project->fresh(), validContent(), 1);
}

it('renders every layout variant with a data-layout hook', function (string $layout) {
    $html = renderDraftWith(['layout' => $layout]);

    expect($html)->toContain('data-layout="'.$layout.'"')
        ->toContain('layout-'.$layout)
        ->toContain('DRAF SAMPEL');
})->with(DesignResolver::LAYOUTS);

it('renders every header + footer variant', function () {
    foreach (DesignResolver::HEADERS as $h) {
        expect(renderDraftWith(['header_style' => $h]))
            ->toContain('hdr-'.$h)->toContain('data-header="'.$h.'"');
    }
    foreach (DesignResolver::FOOTERS as $f) {
        expect(renderDraftWith(['footer_style' => $f]))->toContain('</footer>');
    }
    expect(renderDraftWith(['footer_style' => 'tiga-lajur']))->toContain('ftr-tiga-lajur');
});

it('falls back to defaults for unknown variant values — never breaks render', function () {
    $html = renderDraftWith([
        'layout' => 'tak-wujud', 'header_style' => 'xxx', 'footer_style' => 'yyy',
        'card_style' => 'zzz', 'divider' => 'qqq',
    ]);

    expect($html)->toContain('data-layout="hero-tengah"')   // default
        ->toContain('hdr-padat')
        ->toContain('card-lembut')
        ->toContain('DRAF SAMPEL')
        ->not->toContain('divider-qqq');
});

it('applies card style, divider and animations when set', function () {
    $html = renderDraftWith(['card_style' => 'terapung', 'divider' => 'garis-emas', 'animations' => true]);

    expect($html)->toContain('card-terapung')
        ->toContain('divider-garis-emas')
        ->toContain('has-anim-fade');   // §Fasa 14: legasi bool true → varian fade
});

it('maps animation variants to body classes (§Fasa 14)', function () {
    // Semak kelas BODY (definisi CSS .has-anim-* sentiasa hadir dalam stylesheet).
    expect(renderDraftWith(['animations' => 'zoom']))->toContain('<body class="card-lembut has-anim-zoom ');

    // false / tiada / nilai luar allowlist → body tiada kelas animasi.
    expect(renderDraftWith(['animations' => false]))->toContain('<body class="card-lembut"');
    expect(renderDraftWith(['animations' => 'tiada']))->toContain('<body class="card-lembut"');
    expect(renderDraftWith(['animations' => 'melompat']))->toContain('<body class="card-lembut"');
});
