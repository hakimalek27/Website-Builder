<?php

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Models\Generation;
use App\Models\ProjectSection;
use App\Models\TweakRequest;
use App\Models\User;
use App\Services\BriefBuilder;
use Database\Seeders\DesignPackageSeeder;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// §Fasa 13 W6 — admin nampak prompt jurutera + pecahan kos + HTML + tweak.

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

function htmlGenProject(): array
{
    Storage::fake('local');
    [$project, $token] = picSession(['status' => ProjectStatus::DraftReady]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => ['official_name' => 'Masjid Ujian', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur']]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_2', 'data' => ['mood' => 'tenang_khusyuk', 'design_package' => 'warisan_hijau']]);
    Storage::disk('local')->put('drafts/x/gen.html', '<!DOCTYPE html><html><body>DRAF</body></html>');
    $gen = Generation::factory()->succeeded()->for($project)->create([
        'rendered_path' => 'drafts/x/gen.html',
        'tokens_in' => 7000, 'tokens_out' => 22000, 'cost_estimate' => 0.1722,
        'input_snapshot' => [
            'pipeline' => 'html',
            'engineered_prompt' => 'Bina draf HTML Masjid Ujian dengan warna hijau dan letak [[CONTACT_STRIP]].',
            'stage1' => ['source' => 'ai', 'provider' => 'GPT Jurutera', 'model' => 'gpt-5.5', 'tokens_in' => 4000, 'tokens_out' => 2000, 'cost' => 0.08],
            'stage2' => ['provider' => 'GLM Draf', 'model' => 'glm-5.2', 'tokens_in' => 3000, 'tokens_out' => 20000, 'attempts' => 1],
        ],
    ]);

    return [$project, $gen];
}

it('downloads the engineered prompt for authed admins', function () {
    $this->actingAs(User::factory()->create());
    [$project, $gen] = htmlGenProject();

    $this->get(route('admin.prompt', $gen))
        ->assertOk()
        ->assertSee('Bina draf HTML Masjid Ujian');
});

it('blocks guests from the prompt download and 404s for a shell gen', function () {
    [$project, $gen] = htmlGenProject();
    $this->get(route('admin.prompt', $gen))->assertForbidden();

    $this->actingAs(User::factory()->create());
    $shell = Generation::factory()->succeeded()->for($project)->create(['input_snapshot' => ['pipeline' => 'shell']]);
    $this->get(route('admin.prompt', $shell))->assertNotFound();
});

it('downloads the html draft as an attachment', function () {
    $this->actingAs(User::factory()->create());
    [$project, $gen] = htmlGenProject();

    $res = $this->get(route('admin.draf.muat', $gen))->assertOk();
    expect($res->headers->get('content-disposition'))->toContain('attachment');
});

it('shows the prompt and cost breakdown in the admin infolist', function () {
    $this->actingAs(User::factory()->create());
    [$project, $gen] = htmlGenProject();

    Livewire::test(ViewProject::class, ['record' => $project->id])
        ->assertOk()
        ->assertSee('Peringkat 1')
        ->assertSee('gpt-5.5')
        ->assertSee('Bina draf HTML Masjid Ujian');   // pratonton prompt
});

it('includes the engineered prompt and tweak thread in the brief', function () {
    [$project, $gen] = htmlGenProject();
    TweakRequest::create([
        'project_id' => $project->id, 'base_generation_id' => $gen->id,
        'categories' => ['warna'], 'message' => 'Tukar ke biru',
    ]);

    $md = app(BriefBuilder::class)->markdown($project->fresh());

    expect($md)
        ->toContain('Prompt Jurutera Terkini')
        ->toContain('Bina draf HTML Masjid Ujian')
        ->toContain('Thread Tweak PIC')
        ->toContain('Tukar ke biru');
});
