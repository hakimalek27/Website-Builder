<?php

namespace App\Support;

/**
 * §8.8 — peta kadar harga rasmi setiap model (USD per 1,000,000 token).
 *
 * TUJUAN: cadangan auto-isi borang AiProvider sahaja. Kadar sebenar KEKAL
 * disimpan dalam ai_providers.meta (sumber kebenaran; admin boleh edit) —
 * §8.8 "JANGAN hard-code harga". Model yang tidak pasti = null (isi manual).
 *
 * Semua nilai disahkan dari laman rasmi vendor pada AS_OF (Julai 2026).
 * Padanan: tepat → prefix terpanjang → wildcard '*' → null.
 */
final class ModelRates
{
    public const AS_OF = '2026-07';

    /** @var array<string, string> vendor => URL harga rasmi */
    public const SOURCES = [
        'openai' => 'https://openai.com/api/pricing/',
        'anthropic' => 'https://platform.claude.com/docs/en/about-claude/pricing',
        'openrouter' => 'https://openrouter.ai/models',
        'deepseek' => 'https://api-docs.deepseek.com/quick_start/pricing',
        'zhipu' => 'https://docs.z.ai/guides/overview/pricing',
        'groq' => 'https://groq.com/pricing/',
        'mistral' => 'https://mistral.ai/pricing',
        'google' => 'https://ai.google.dev/gemini-api/docs/pricing',
        'ollama' => 'https://ollama.com',
        'custom' => '',
    ];

    /**
     * vendor => [ model-key => [in, out] ] USD/MTok.
     * Model tidak disenarai = tiada kadar auto (null → isi manual).
     *
     * @var array<string, array<string, array{0: float, 1: float}>>
     */
    private const RATES = [
        // sumber: https://openai.com/api/pricing/ (2026-07)
        'openai' => [
            'gpt-5.5' => [5.0, 30.0],
            'gpt-5.4-mini' => [0.75, 4.50],
            'gpt-5.4-nano' => [0.20, 1.25],
            'gpt-5.4' => [2.50, 15.0],
            'gpt-5' => [1.25, 10.0],
            'gpt-4.1-mini' => [0.40, 1.60],
            'gpt-4.1-nano' => [0.10, 0.40],
            'gpt-4.1' => [2.0, 8.0],
            'gpt-4o-mini' => [0.15, 0.60],
            'gpt-4o' => [2.50, 10.0],
        ],
        // sumber: https://platform.claude.com/docs/en/about-claude/pricing (2026-07)
        'anthropic' => [
            'claude-opus-4-8' => [5.0, 25.0],
            'claude-sonnet-5' => [3.0, 15.0],
            'claude-haiku-4-5' => [1.0, 5.0],
        ],
        // sumber: https://api-docs.deepseek.com/quick_start/pricing (2026-07) — chat/reasoner → V4 Flash
        'deepseek' => [
            'deepseek-v4-pro' => [1.74, 3.48],
            'deepseek-v4-flash' => [0.14, 0.28],
            'deepseek-reasoner' => [0.14, 0.28],
            'deepseek-chat' => [0.14, 0.28],
        ],
        // sumber: https://docs.z.ai/guides/overview/pricing (2026-07)
        'zhipu' => [
            'glm-5.2' => [1.40, 4.40],
        ],
        // sumber: https://ai.google.dev/gemini-api/docs/pricing (2026-07) — kadar konteks ≤200k
        'google' => [
            'gemini-2.5-pro' => [1.25, 10.0],
            'gemini-2.5-flash' => [0.30, 2.50],
        ],
        // sumber: https://openrouter.ai/models (2026-07) — cermin kadar vendor asal
        'openrouter' => [
            'openai/gpt-5.5' => [5.0, 30.0],
            'anthropic/claude-sonnet-5' => [3.0, 15.0],
            'deepseek/deepseek-chat' => [0.14, 0.28],
            'google/gemini-2.5-pro' => [1.25, 10.0],
        ],
        // Ollama = tempatan (percuma).
        'ollama' => [
            '*' => [0.0, 0.0],
        ],
    ];

    /**
     * Kadar USD/MTok untuk (vendor, model), atau null jika tiada kadar auto.
     *
     * @return array{in: float, out: float}|null
     */
    public static function for(string $vendor, string $model): ?array
    {
        $vendor = strtolower(trim($vendor));
        $model = trim($model);
        $table = self::RATES[$vendor] ?? [];

        if ($model !== '' && isset($table[$model])) {
            return ['in' => $table[$model][0], 'out' => $table[$model][1]];
        }

        // Prefix terpanjang (cth 'claude-haiku-4-5-20251001' → 'claude-haiku-4-5').
        $best = null;
        $bestLen = -1;
        if ($model !== '') {
            foreach ($table as $key => $rate) {
                if ($key === '*') {
                    continue;
                }
                if (str_starts_with($model, $key) && strlen($key) > $bestLen) {
                    $best = $rate;
                    $bestLen = strlen($key);
                }
            }
        }
        if ($best !== null) {
            return ['in' => $best[0], 'out' => $best[1]];
        }

        if (isset($table['*'])) {
            return ['in' => $table['*'][0], 'out' => $table['*'][1]];
        }

        return null;
    }

    /** URL sumber harga rasmi untuk vendor (helper text borang). */
    public static function source(string $vendor): ?string
    {
        $url = self::SOURCES[strtolower(trim($vendor))] ?? null;

        return $url === '' ? null : $url;
    }
}
