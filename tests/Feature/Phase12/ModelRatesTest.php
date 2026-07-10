<?php

use App\Support\ModelRates;

// Fasa 12 W5 — peta kadar harga model (USD/MTok).

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

it('returns null for unpriced vendor/model (manual entry)', function () {
    expect(ModelRates::for('mistral', 'mistral-large-latest'))->toBeNull();
    expect(ModelRates::for('custom', 'apa-apa'))->toBeNull();
    expect(ModelRates::for('zhipu', 'glm-4.5-flash'))->toBeNull();
});

it('exposes an official source URL per vendor', function () {
    expect(ModelRates::source('openai'))->toContain('openai.com');
    expect(ModelRates::source('anthropic'))->toContain('claude.com');
    expect(ModelRates::source('custom'))->toBeNull();
});
