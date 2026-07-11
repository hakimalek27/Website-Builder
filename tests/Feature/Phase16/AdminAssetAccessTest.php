<?php

use App\Models\Asset;
use App\Models\Project;
use App\Models\User;
use App\Services\AssetZipper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// §Fasa 16 W3 — akses aset admin penuh (semua kind) + ZIP.

function makeAsset(Project $project, string $kind, string $ext = 'jpg'): Asset
{
    $path = 'assets/'.$project->id.'/'.Str::ulid().'.'.$ext;
    Storage::disk('local')->put($path, 'FILEDATA-'.$kind);

    return Asset::create([
        'project_id' => $project->id, 'kind' => $kind, 'path' => $path,
        'original_name' => $kind.' asal.'.$ext, 'mime' => $ext === 'pdf' ? 'application/pdf' : 'image/jpeg',
        'size' => 13, 'sort' => 0,
    ]);
}

it('serves every asset kind to an authenticated admin', function () {
    Storage::fake('local');
    $project = Project::factory()->create();

    foreach (['qr', 'doc', 'committee_photo'] as $kind) {
        $asset = makeAsset($project, $kind, $kind === 'doc' ? 'pdf' : 'jpg');
        $this->actingAs(User::factory()->create())
            ->get(route('admin.aset', ['asset' => $asset]))
            ->assertOk();
    }
});

it('forbids guests from downloading assets', function () {
    Storage::fake('local');
    $project = Project::factory()->create();
    $asset = makeAsset($project, 'committee_photo');

    $this->get(route('admin.aset', ['asset' => $asset]))->assertForbidden();
});

it('zips all assets with kind-prefixed entries', function () {
    Storage::fake('local');
    $project = Project::factory()->create(['short_name' => 'PERKIB']);
    makeAsset($project, 'logo', 'png');
    makeAsset($project, 'committee_photo');
    makeAsset($project, 'doc', 'pdf');

    $result = app(AssetZipper::class)->zipFor($project);
    expect(Storage::disk('local')->exists($result['path']))->toBeTrue();
    expect($result['name'])->toContain('perkib');

    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($result['path']));
    $names = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $names[] = $zip->getNameIndex($i);
    }
    $zip->close();

    expect($names)->toHaveCount(3);
    expect(collect($names)->contains(fn ($n) => str_starts_with($n, 'logo/')))->toBeTrue();
    expect(collect($names)->contains(fn ($n) => str_starts_with($n, 'doc/')))->toBeTrue();
    expect(collect($names)->contains(fn ($n) => str_starts_with($n, 'committee_photo/')))->toBeTrue();
});

it('throws when the project has no assets', function () {
    Storage::fake('local');
    $project = Project::factory()->create();

    expect(fn () => app(AssetZipper::class)->zipFor($project))->toThrow(RuntimeException::class);
});
