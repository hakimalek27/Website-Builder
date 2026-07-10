<?php

use App\Enums\ProjectStatus;
use App\Models\Generation;

// Fasa 12 W1 — navigasi PIC status-aware + baris draf boleh diklik.

it('hides the Jana Draf link before a project is submitted', function () {
    [, $token] = picSession(['status' => ProjectStatus::InProgress]);

    $res = $this->get(route('pic.home', ['token' => $token]));

    $res->assertOk()
        ->assertDontSee(route('pic.jana', ['token' => $token]))
        ->assertSee(route('pic.semak', ['token' => $token]))   // nav asas kekal
        ->assertSee(route('pic.status', ['token' => $token]));
});

it('shows the full nav incl. Jana Draf and Draf once a draft exists', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::DraftReady]);
    $gen = Generation::factory()->succeeded()->for($project)->create(['rendered_path' => 'drafts/a/b.html']);

    $res = $this->get(route('pic.home', ['token' => $token]));

    $res->assertOk()
        ->assertSee(route('pic.jana', ['token' => $token]))
        ->assertSee(route('pic.draf', ['token' => $token, 'generation' => $gen->id]))
        ->assertSee(route('pic.status', ['token' => $token]));
});

it('links the JanaHub previous-draft row to the draft viewer', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::DraftReady]);
    $gen = Generation::factory()->succeeded()->for($project)->create(['rendered_path' => 'drafts/a/b.html']);

    $res = $this->get(route('pic.jana', ['token' => $token]));

    $res->assertOk()
        ->assertSee(route('pic.draf', ['token' => $token, 'generation' => $gen->id]))
        ->assertSee('Lihat Draf');
});

it('shows post-submit shortcut cards on the PIC home', function () {
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted]);
    Generation::factory()->succeeded()->for($project)->create(['rendered_path' => 'drafts/a/b.html']);

    $res = $this->get(route('pic.home', ['token' => $token]));

    $res->assertOk()
        ->assertSee('Draf laman anda')
        ->assertSee(route('pic.jana', ['token' => $token]));
});

it('does not render the shortcut cards while still in progress', function () {
    [, $token] = picSession(['status' => ProjectStatus::InProgress]);

    $this->get(route('pic.home', ['token' => $token]))
        ->assertOk()
        ->assertDontSee('Draf laman anda');
});
