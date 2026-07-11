<?php

use App\Filament\Resources\AiProviders\Pages\CreateAiProvider;
use App\Models\User;
use Livewire\Livewire;

// §8.1 — borang reaktif: pilih penyedia → base URL + driver diisi automatik (elak 404).

beforeEach(fn () => $this->actingAs(User::factory()->create()));

it('auto-fills base URL and driver when DeepSeek is chosen', function () {
    Livewire::test(CreateAiProvider::class)
        ->fillForm(['vendor' => 'deepseek'])
        ->assertFormSet([
            'driver' => 'openai_compatible',
            'base_url' => 'https://api.deepseek.com',
        ]);
});

it('auto-fills the OpenRouter base URL (the 404 case)', function () {
    Livewire::test(CreateAiProvider::class)
        ->fillForm(['vendor' => 'openrouter'])
        ->assertFormSet(['base_url' => 'https://openrouter.ai/api/v1']);
});

it('switches driver to anthropic for the Anthropic vendor', function () {
    Livewire::test(CreateAiProvider::class)
        ->fillForm(['vendor' => 'anthropic'])
        ->assertFormSet([
            'driver' => 'anthropic',
            'base_url' => 'https://api.anthropic.com',
        ]);
});

it('auto-fills USD rates when a known model is chosen (Fasa 12 W5)', function () {
    Livewire::test(CreateAiProvider::class)
        ->fillForm(['vendor' => 'openai'])
        ->fillForm(['model' => 'gpt-5.5'])
        ->assertFormSet([
            'meta' => ['rate_in_per_mtok' => '5', 'rate_out_per_mtok' => '30', 'currency' => 'USD'],
        ]);
});

it('leaves rates blank for an unpriced model (Fasa 12 W5)', function () {
    // §Fasa 14 — guna model yang benar-benar tiada kadar (dropdown penuh kini berkadar).
    Livewire::test(CreateAiProvider::class)
        ->fillForm(['vendor' => 'mistral'])
        ->fillForm(['model' => 'model-tersuai-tak-wujud'])
        ->assertFormSet([
            'meta' => ['rate_in_per_mtok' => '', 'rate_out_per_mtok' => '', 'currency' => 'USD'],
        ]);
});

it('saves a GLM (Zhipu) provider with only vendor, key and model supplied', function () {
    Livewire::test(CreateAiProvider::class)
        ->fillForm([
            'name' => 'GLM Utama',
            'vendor' => 'zhipu',
            'api_key' => 'sk-demo-key',
            'model' => 'glm-5.2',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('ai_providers', [
        'name' => 'GLM Utama',
        'vendor' => 'zhipu',
        'driver' => 'openai_compatible',
        'base_url' => 'https://api.z.ai/api/paas/v4',
        'model' => 'glm-5.2',
    ]);
});
