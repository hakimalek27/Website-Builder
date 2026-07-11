<?php

use App\Enums\AiVendor;
use App\Support\ModelRates;

// Fasa 12 W5 — peta kadar harga model (USD/MTok). Fasa 14 — liputan penuh dropdown.

it('returns confirmed rates for the flagship models', function () {
    expect(ModelRates::for('openai', 'gpt-5.5'))->toBe(['in' => 5.0, 'out' => 30.0]);
    expect(ModelRates::for('anthropic', 'claude-opus-4-8'))->toBe(['in' => 5.0, 'out' => 25.0]);
    expect(ModelRates::for('zhipu', 'glm-5.2'))->toBe(['in' => 1.40, 'out' => 4.40]);
});

it('does not let a shorter prefix override an exact match', function () {
    // gpt-5.5 mesti guna entri sendiri, bukan prefix 'gpt-5'.
    expect(ModelRates::for('openai', 'gpt-5.5'))->not->toBe(ModelRates::for('openai', 'gpt-5'));
    expect(ModelRates::for('openai', 'gpt-5'))->toBe(['in' => 1.25, 'out' => 10.0]);
});

it('matches dated model IDs by longest prefix', function () {
    expect(ModelRates::for('anthropic', 'claude-haiku-4-5-20251001'))->toBe(['in' => 1.0, 'out' => 5.0]);
    expect(ModelRates::for('openai', 'gpt-5-chat-latest'))->toBe(['in' => 1.25, 'out' => 10.0]);
});

it('returns a zero wildcard rate for Ollama (local)', function () {
    expect(ModelRates::for('ollama', 'llama3.2'))->toBe(['in' => 0.0, 'out' => 0.0]);
});

it('returns null only for genuinely unpriced vendor/model (manual entry)', function () {
    expect(ModelRates::for('custom', 'apa-apa'))->toBeNull();
    expect(ModelRates::for('zhipu', 'glm-model-tak-wujud'))->toBeNull();
});

it('prices newly added models (Fasa 14)', function () {
    expect(ModelRates::for('groq', 'llama-3.3-70b-versatile'))->toBe(['in' => 0.59, 'out' => 0.79]);
    expect(ModelRates::for('mistral', 'mistral-large-latest'))->toBe(['in' => 2.0, 'out' => 6.0]);
    expect(ModelRates::for('google', 'gemini-2.5-flash-lite'))->toBe(['in' => 0.10, 'out' => 0.40]);
    expect(ModelRates::for('openrouter', 'x-ai/grok-4'))->toBe(['in' => 3.0, 'out' => 15.0]);
    expect(ModelRates::for('zhipu', 'glm-4.6'))->toBe(['in' => 0.43, 'out' => 1.74]);
    expect(ModelRates::for('zhipu', 'glm-4.5-flash'))->toBe(['in' => 0.0, 'out' => 0.0]); // percuma
});

it('has an auto rate for EVERY model in EVERY vendor dropdown (§Fasa 14)', function () {
    foreach (AiVendor::cases() as $vendor) {
        if ($vendor === AiVendor::Custom) {
            continue; // Custom = OpenAI-compatible generik, tiada senarai model
        }
        foreach ($vendor->models() as $model) {
            expect(ModelRates::for($vendor->value, $model))
                ->not->toBeNull("Model {$vendor->value}/{$model} tiada kadar auto — tambah dalam ModelRates.");
        }
    }
});

it('exposes an official source URL per vendor', function () {
    expect(ModelRates::source('openai'))->toContain('openai.com');
    expect(ModelRates::source('anthropic'))->toContain('claude.com');
    expect(ModelRates::source('groq'))->toContain('groq.com');
    expect(ModelRates::source('mistral'))->toContain('mistral.ai');
    expect(ModelRates::source('custom'))->toBeNull();
});
