<?php

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Note;
use App\Models\Project;
use App\Models\ProjectSection;
use App\Models\User;
use App\Services\BriefBuilder;
use Database\Seeders\DesignPackageSeeder;
use Livewire\Livewire;

// Fasa 12 W3 — brief MD penuh untuk admin/AI pembina.

beforeEach(fn () => $this->seed(DesignPackageSeeder::class));

function briefProject(): Project
{
    [$project] = picSession(['status' => ProjectStatus::Submitted]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'official_name' => 'Masjid Ujian', 'city' => 'KL', 'state' => 'W.P. Kuala Lumpur', 'phone_primary' => '03-1234 5678',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'infaq' => ['bank_name' => 'Bank Islam', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian'],
    ]]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_9', 'data' => ['free_notes' => 'Saya nak laman moden dan ringkas.']]);
    Note::create(['project_id' => $project->id, 'author' => 'pic', 'author_name' => 'PIC', 'kind' => 'general', 'body' => 'Tolong guna warna hijau.']);

    return $project->fresh();
}

it('builds a complete brief with full bank, notes and AI-builder instructions', function () {
    $md = app(BriefBuilder::class)->markdown(briefProject());

    expect($md)
        ->toContain('ARAHAN UNTUK AI PEMBINA')
        ->toContain('Masjid Ujian')
        ->toContain('1234567890')                 // bank PENUH (dokumen dalaman, tidak bermask)
        ->toContain('Saya nak laman moden')        // free_notes
        ->toContain('Tolong guna warna hijau')     // thread nota
        ->toContain('JAKIM e-Solat');              // peraturan waktu solat (masjid)
});

it('names the brief file by slug and date', function () {
    expect(app(BriefBuilder::class)->fileName(briefProject()))
        ->toStartWith('brief-')->toEndWith('.md');
});

it('shows the brief action for a submitted project but hides it before submission', function () {
    $this->actingAs(User::factory()->create());
    $submitted = briefProject();
    [$draft] = picSession(['status' => ProjectStatus::InProgress]);

    Livewire::test(ListProjects::class)
        ->assertOk()
        ->assertTableActionVisible('brief', $submitted)
        ->assertTableActionHidden('brief', $draft);
});
