<?php

use App\Livewire\Wizard\WizardStep;
use App\Models\Asset;
use App\Models\ProjectSection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// Fasa 11 — muat naik imej hero berbilang (§6 L6).

beforeEach(fn () => Storage::fake('local'));

function heroSection($project): array
{
    return ProjectSection::where('project_id', $project->id)->where('section_key', 'step_6')->first()->data['hero_files'] ?? [];
}

it('stores multiple hero images in one selection', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 6])
        ->set('data.hero_mode', 'upload')
        ->set('files.hero', [
            UploadedFile::fake()->image('a.jpg', 1600, 900),
            UploadedFile::fake()->image('b.jpg', 1600, 900),
        ]);

    expect(heroSection($project))->toHaveCount(2)
        ->and(Asset::where('project_id', $project->id)->where('kind', 'hero')->count())->toBe(2);
});

it('enforces a maximum of 3 hero images', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 6])
        ->set('data.hero_mode', 'upload')
        ->set('files.hero', [
            UploadedFile::fake()->image('a.jpg', 1600, 900),
            UploadedFile::fake()->image('b.jpg', 1600, 900),
            UploadedFile::fake()->image('c.jpg', 1600, 900),
            UploadedFile::fake()->image('d.jpg', 1600, 900),
        ])
        ->assertHasErrors('files.hero');

    expect(heroSection($project))->toHaveCount(3);
});

it('removes a hero image and deletes its asset + file', function () {
    [$project, $token] = picSession();

    $component = Livewire::test(WizardStep::class, ['token' => $token, 'step' => 6])
        ->set('data.hero_mode', 'upload')
        ->set('files.hero', [
            UploadedFile::fake()->image('a.jpg', 1600, 900),
            UploadedFile::fake()->image('b.jpg', 1600, 900),
        ]);

    $firstId = heroSection($project)[0]['asset_id'];
    $firstPath = Asset::find($firstId)->path;
    expect(Storage::disk('local')->exists($firstPath))->toBeTrue();

    $component->call('removeHeroFile', 0);

    expect(heroSection($project))->toHaveCount(1)
        ->and(Asset::find($firstId))->toBeNull()
        ->and(Storage::disk('local')->exists($firstPath))->toBeFalse();
});

it('serves a project asset over the token route and 404s for other projects', function () {
    [$project, $token] = picSession();

    Livewire::test(WizardStep::class, ['token' => $token, 'step' => 6])
        ->set('data.hero_mode', 'upload')
        ->set('files.hero', [UploadedFile::fake()->image('a.jpg', 1600, 900)]);

    $asset = Asset::where('project_id', $project->id)->where('kind', 'hero')->firstOrFail();

    $this->get("/b/{$token}/aset/{$asset->id}")->assertOk();

    [$other, $otherToken] = picSession();
    $this->get("/b/{$otherToken}/aset/{$asset->id}")->assertNotFound();
});
