<?php

use App\Enums\Tier;
use App\Models\ProjectDesign;
use App\Services\DraftStyleDirector;

// §Fasa 15 W2 — pengarah gaya deterministik (anti-pendua).

function directorFor(array $attrs = []): array
{
    [$project] = picSession($attrs);

    return app(DraftStyleDirector::class)->directives($project->fresh());
}

it('produces a stable set of directives for the same project', function () {
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    $director = app(DraftStyleDirector::class);

    $a = $director->directives($project);
    $b = $director->directives($project);
    expect($a)->toBe($b);
    expect($a)->toHaveKeys(['seed', 'hero_treatment', 'motif', 'ornament', 'cta_style', 'section_rhythm', 'blueprints', 'keunikan']);
});

it('gives different projects different style seeds', function () {
    $seeds = collect(range(1, 6))->map(fn () => directorFor(['tier' => Tier::MasjidKariah])['seed'])->unique();
    // ULID berbeza → crc32 berbeza → tidak semua seed sama.
    expect($seeds->count())->toBeGreaterThan(1);
});

it('honours an explicit style_seed override', function () {
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    ProjectDesign::updateOrCreate(
        ['project_id' => $project->id],
        ['package_key' => 'warisan_hijau', 'overrides' => ['style_seed' => 42]],
    );

    expect(app(DraftStyleDirector::class)->directives($project->fresh())['seed'])->toBe(42);
});

it('selects a hero blueprint matching the chosen layout', function () {
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    ProjectDesign::updateOrCreate(
        ['project_id' => $project->id],
        ['package_key' => 'warisan_hijau', 'overrides' => ['layout' => 'hero-mihrab', 'style_seed' => 1]],
    );

    $dir = app(DraftStyleDirector::class)->directives($project->fresh());
    expect($dir['blueprints'][0])->toBe('hero-mihrab.html');
});

it('bundles blueprint content that references kit classes and tokens', function () {
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    $bundle = app(DraftStyleDirector::class)->blueprintBundle($project);

    expect($bundle)->toContain('rk-hero')->toContain('rk-footer')->toContain('[[CONTACT_STRIP]]');
});

it('picks an ngo hero scene for an ngo project', function () {
    [$project] = picSession(['tier' => Tier::NgoKomuniti]);
    $dir = app(DraftStyleDirector::class)->directives($project->fresh());
    expect($dir['stock'])->not->toBeNull();
    expect($dir['stock']['tier'])->not->toBe('masjid');   // NGO tak dapat scene masjid-sahaja
});
