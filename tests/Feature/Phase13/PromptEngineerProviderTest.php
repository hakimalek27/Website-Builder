<?php

use App\Filament\Resources\AiProviders\Pages\CreateAiProvider;
use App\Models\AiProvider;
use App\Models\User;
use Livewire\Livewire;

// §Fasa 13 W1 — penyedia Jurutera Prompt (Peringkat 1, satu sahaja).

it('keeps only one prompt-engineer provider (booted enforcement)', function () {
    $first = AiProvider::factory()->create(['is_prompt_engineer' => true]);
    $second = AiProvider::factory()->create(['is_prompt_engineer' => true]);

    expect($first->fresh()->is_prompt_engineer)->toBeFalse();
    expect($second->fresh()->is_prompt_engineer)->toBeTrue();
});

it('does not disturb the default flag when setting the engineer flag', function () {
    $default = AiProvider::factory()->create(['is_default' => true]);
    AiProvider::factory()->create(['is_prompt_engineer' => true]);

    expect($default->fresh()->is_default)->toBeTrue();
});

it('promptEngineer() returns the active flagged provider and ignores inactive', function () {
    AiProvider::factory()->create(['is_prompt_engineer' => true, 'is_active' => false]);
    expect(AiProvider::promptEngineer())->toBeNull();

    $active = AiProvider::factory()->create(['is_prompt_engineer' => true, 'is_active' => true]);
    expect(AiProvider::promptEngineer()?->id)->toBe($active->id);
});

it('saves the prompt-engineer toggle from the Filament form', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateAiProvider::class)
        ->fillForm([
            'name' => 'GPT Jurutera',
            'vendor' => 'openai',
            'api_key' => 'sk-demo',
            'model' => 'gpt-5.5',
            'is_prompt_engineer' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('ai_providers', ['name' => 'GPT Jurutera', 'is_prompt_engineer' => true]);
});
