<?php

namespace App\Enums;

/**
 * Penyedia AI pratetap (§8.1) — memetakan penyedia → driver + base URL + senarai model.
 *
 * Tujuan: admin cuma pilih penyedia + masukkan API key + pilih model. Base URL diisi
 * automatik supaya tiada lagi ralat 404 kerana base URL salah.
 *
 * Base URL disahkan dari dokumentasi rasmi (Julai 2026). Senarai model = cadangan
 * datalist sahaja (boleh taip nilai lain) kerana model kerap berubah.
 */
enum AiVendor: string
{
    case OpenAi = 'openai';
    case Anthropic = 'anthropic';
    case OpenRouter = 'openrouter';
    case DeepSeek = 'deepseek';
    case Zhipu = 'zhipu';
    case Groq = 'groq';
    case Mistral = 'mistral';
    case Google = 'google';
    case Ollama = 'ollama';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::OpenAi => 'OpenAI',
            self::Anthropic => 'Anthropic (Claude)',
            self::OpenRouter => 'OpenRouter',
            self::DeepSeek => 'DeepSeek',
            self::Zhipu => 'GLM · Z.ai (Zhipu)',
            self::Groq => 'Groq',
            self::Mistral => 'Mistral',
            self::Google => 'Google Gemini',
            self::Ollama => 'Ollama (local)',
            self::Custom => 'Custom (OpenAI-compatible)',
        };
    }

    /** Driver teknikal — Anthropic guna /v1/messages; selainnya OpenAI-compatible /chat/completions. */
    public function driver(): AiDriver
    {
        return $this === self::Anthropic ? AiDriver::Anthropic : AiDriver::OpenAiCompatible;
    }

    /** Base URL rasmi (Julai 2026). Kosong = admin isi sendiri (Custom). */
    public function baseUrl(): string
    {
        return match ($this) {
            self::OpenAi => 'https://api.openai.com/v1',
            self::Anthropic => 'https://api.anthropic.com',
            self::OpenRouter => 'https://openrouter.ai/api/v1',
            self::DeepSeek => 'https://api.deepseek.com',
            self::Zhipu => 'https://api.z.ai/api/paas/v4',
            self::Groq => 'https://api.groq.com/openai/v1',
            self::Mistral => 'https://api.mistral.ai/v1',
            self::Google => 'https://generativelanguage.googleapis.com/v1beta/openai',
            self::Ollama => 'http://localhost:11434/v1',
            self::Custom => '',
        };
    }

    /**
     * Cadangan model (datalist) — bukan senarai muktamad; admin boleh taip nilai lain.
     *
     * @return array<int, string>
     */
    public function models(): array
    {
        return match ($this) {
            self::OpenAi => ['gpt-5.5', 'gpt-5.4-mini', 'gpt-5.4-nano', 'gpt-5-chat-latest', 'gpt-5', 'gpt-4.1', 'gpt-4o', 'gpt-4o-mini'],
            self::Anthropic => ['claude-opus-4-8', 'claude-sonnet-5', 'claude-haiku-4-5-20251001', 'claude-fable-5'],
            self::OpenRouter => ['openai/gpt-5.5', 'z-ai/glm-5.2', 'anthropic/claude-sonnet-5', 'deepseek/deepseek-chat', 'google/gemini-2.5-pro', 'z-ai/glm-4.6', 'x-ai/grok-4', 'meta-llama/llama-3.3-70b-instruct', 'qwen/qwen-2.5-72b-instruct'],
            self::DeepSeek => ['deepseek-chat', 'deepseek-reasoner', 'deepseek-v4-pro', 'deepseek-v4-flash'],
            self::Zhipu => ['glm-5.2', 'glm-5.1', 'glm-4.7', 'glm-4.6', 'glm-4.5-air', 'glm-4.5-flash'],
            self::Groq => ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'openai/gpt-oss-120b', 'moonshotai/kimi-k2-instruct', 'qwen/qwen3-32b'],
            self::Mistral => ['mistral-large-latest', 'mistral-small-latest', 'magistral-medium-latest', 'ministral-8b-latest', 'pixtral-large-latest'],
            self::Google => ['gemini-2.5-pro', 'gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-2.0-flash'],
            self::Ollama => ['llama3.2', 'qwen2.5', 'mistral', 'gemma3', 'phi4'],
            self::Custom => [],
        };
    }

    /** URL untuk dapatkan API key (helper text). */
    public function apiKeyUrl(): ?string
    {
        return match ($this) {
            self::OpenAi => 'https://platform.openai.com/api-keys',
            self::Anthropic => 'https://console.anthropic.com/settings/keys',
            self::OpenRouter => 'https://openrouter.ai/keys',
            self::DeepSeek => 'https://platform.deepseek.com/api_keys',
            self::Zhipu => 'https://z.ai/manage-apikey/apikey-list',
            self::Groq => 'https://console.groq.com/keys',
            self::Mistral => 'https://console.mistral.ai/api-keys',
            self::Google => 'https://aistudio.google.com/apikey',
            self::Ollama, self::Custom => null,
        };
    }

    /** Pilihan untuk Select Filament. @return array<string, string> */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $v) => $carry + [$v->value => $v->label()],
            [],
        );
    }
}
