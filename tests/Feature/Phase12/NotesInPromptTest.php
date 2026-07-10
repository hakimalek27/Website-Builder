<?php

use App\Enums\Tier;
use App\Models\Note;
use App\Models\ProjectSection;
use App\Services\Ai\PromptBuilder;

// Fasa 12 W4 — nota & citarasa PIC (step-7/9 + thread nota) mengalir ke prompt (PII di-scrub).

function projectWithNotes(): array
{
    [$project, $token] = picSession(['tier' => Tier::MasjidKariah]);
    enablePages($project, ['utama', 'hubungi']);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_7', 'data' => [
        'liked_refs' => [['url' => 'https://masjidcontoh.my', 'what_liked' => 'reka bentuk bersih']],
        'dislikes' => 'Jangan guna warna terlalu terang.',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_9', 'data' => [
        'free_notes' => 'Saya nak laman moden. Hubungi saya di ali@example.com atau 0123456789.',
    ]]);
    foreach (range(1, 6) as $i) {
        Note::create(['project_id' => $project->id, 'author' => 'pic', 'author_name' => 'PIC', 'kind' => 'general', 'body' => "Nota PIC nombor {$i}"]);
    }
    Note::create(['project_id' => $project->id, 'author' => 'admin', 'author_name' => 'Admin', 'kind' => 'general', 'body' => 'Balasan admin rahsia']);

    return [$project, $token];
}

it('includes PIC notes, references and dislikes in the prompt', function () {
    [$project] = projectWithNotes();
    $u = app(PromptBuilder::class)->build($project)['user'];

    expect($u)->toContain('NOTA & CITARASA PIC')
        ->toContain('masjidcontoh.my')
        ->toContain('reka bentuk bersih')
        ->toContain('Jangan guna warna terlalu terang')
        ->toContain('laman moden');
});

it('scrubs emails and long digit runs from PIC free text', function () {
    [$project] = projectWithNotes();
    $u = app(PromptBuilder::class)->build($project)['user'];

    expect($u)->not->toContain('ali@example.com')
        ->not->toContain('0123456789')
        ->toContain('[emel dibuang]')
        ->toContain('[nombor dibuang]');
});

it('feeds at most the 5 latest PIC notes and excludes admin replies', function () {
    [$project] = projectWithNotes();
    $u = app(PromptBuilder::class)->build($project)['user'];

    expect(substr_count($u, 'Nota PIC nombor'))->toBe(5)
        ->and($u)->not->toContain('Balasan admin rahsia');
});

it('omits the notes block entirely when there is nothing to say', function () {
    [$project] = picSession(['tier' => Tier::MasjidKariah]);
    enablePages($project, ['utama', 'hubungi']);

    $u = app(PromptBuilder::class)->build($project)['user'];

    expect($u)->not->toContain('NOTA & CITARASA PIC');
});
