<?php

use App\Enums\Tier;
use App\Models\Asset;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Models\TemplateCatalog;
use App\Services\BriefBuilder;
use App\Support\ProjectDataPresenter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// §Fasa 16 W5 — brief TEMPLAT RUJUKAN + senarai aset + infolist presenter.

function projectWithTemplate(): Project
{
    [$project] = picSession(['tier' => Tier::MasjidKariah, 'mosque_name' => 'Masjid Ujian', 'short_name' => 'MU']);
    $tpl = TemplateCatalog::factory()->create([
        'name' => 'Muezzin Masjid', 'categories' => ['masjid'], 'style_tags' => ['moden'],
        'description' => 'Tema masjid lengkap.',
    ]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'official_name' => 'Masjid Ujian', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => [
        'mood' => 'tenang_khusyuk', 'template_id' => $tpl->id,
        'template_snapshot' => ['name' => 'Muezzin Masjid', 'url' => 'https://x.test/muezzin', 'demo_url' => 'https://x.test/demo'],
        'template_notes' => ['suka' => 'Warna tenang', 'ubah' => 'Buang slider', 'tambah' => 'Waktu solat besar'],
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_7', 'data' => [
        'liked_refs' => [['url' => 'https://mamkl.my', 'what_liked' => 'Kemas']],
    ]]);

    return $project;
}

it('includes the template reference section in the brief', function () {
    $md = app(BriefBuilder::class)->markdown(projectWithTemplate());

    expect($md)->toContain('TEMPLAT RUJUKAN & ARAHAN DESIGN')
        ->toContain('Muezzin Masjid')
        ->toContain('Warna tenang')
        ->toContain('Next.js + Sanity')
        ->toContain('mamkl.my');
});

it('lists uploaded assets in the brief', function () {
    Storage::fake('local');
    $project = projectWithTemplate();
    Asset::create([
        'project_id' => $project->id, 'kind' => 'committee_photo',
        'path' => 'assets/'.$project->id.'/'.Str::ulid().'.jpg', 'original_name' => 'ajk1.jpg',
        'mime' => 'image/jpeg', 'size' => 204800, 'width' => 800, 'height' => 600, 'sort' => 0,
    ]);

    $md = app(BriefBuilder::class)->markdown($project);

    expect($md)->toContain('SENARAI ASET PENUH')
        ->toContain('committee_photo')
        ->toContain('ajk1.jpg');
});

it('omits template & asset sections when absent', function () {
    [$project] = picSession(['tier' => Tier::MasjidKariah, 'mosque_name' => 'Masjid Kosong']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'official_name' => 'Masjid Kosong', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'jakim_zone' => 'WLY01',
    ]]);

    $md = app(BriefBuilder::class)->markdown($project);

    expect($md)->not->toContain('TEMPLAT RUJUKAN & ARAHAN DESIGN');
    expect($md)->not->toContain('SENARAI ASET PENUH');
});

it('presents template step_2 data without error', function () {
    $blocks = ProjectDataPresenter::all(projectWithTemplate());
    $s2 = collect($blocks)->firstWhere('step', 2);

    expect($s2)->not->toBeNull();
    expect($s2['markdown'])->toContain('Muezzin Masjid');
});
