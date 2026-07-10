<?php

use App\Enums\ProjectStatus;
use App\Livewire\Pic\JanaHub;
use App\Models\Generation;
use Livewire\Livewire;

// §Fasa 13 W5 — progres langsung ikut saluran + baki perubahan AI.

it('shows html progress labels when the active generation is html', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted]);
    Generation::factory()->for($project)->processing()->create(['input_snapshot' => ['pipeline' => 'html']]);

    Livewire::test(JanaHub::class, ['token' => $token])
        ->assertSee('AI sedang menjana draf HTML…');
});

it('shows the classic shell progress labels when the active generation is shell', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted]);
    Generation::factory()->for($project)->processing()->create(['input_snapshot' => ['pipeline' => 'shell']]);

    Livewire::test(JanaHub::class, ['token' => $token])
        ->assertSee('Menganalisa maklumat masjid…')
        ->assertDontSee('AI sedang menjana draf HTML…');
});

it('shows the remaining AI change count', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::DraftReady, 'quota_ai_total' => 3, 'quota_ai_used' => 1]);

    Livewire::test(JanaHub::class, ['token' => $token])
        ->assertSee('Perubahan AI berbaki');
});
