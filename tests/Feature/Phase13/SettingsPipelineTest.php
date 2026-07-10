<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

// §Fasa 13 W1 — tetapan saluran draf.

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('saves the draft pipeline and html max tokens', function () {
    Livewire::test(ManageSettings::class)
        ->fillForm(['draft_pipeline' => 'html', 'html_max_tokens' => '25000'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::get('draft_pipeline'))->toBe('html');
    expect(Setting::get('html_max_tokens'))->toBe('25000');
});

it('can switch back to the shell pipeline', function () {
    Setting::put('draft_pipeline', 'html');

    Livewire::test(ManageSettings::class)
        ->fillForm(['draft_pipeline' => 'shell'])
        ->call('save');

    expect(Setting::get('draft_pipeline'))->toBe('shell');
});
