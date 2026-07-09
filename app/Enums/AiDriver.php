<?php

namespace App\Enums;

// Driver AI — §8.1. Dua sahaja: Anthropic & OpenAI-compatible.
enum AiDriver: string
{
    case Anthropic = 'anthropic';
    case OpenAiCompatible = 'openai_compatible';

    public function label(): string
    {
        return match ($this) {
            self::Anthropic => 'Anthropic (Claude)',
            self::OpenAiCompatible => 'OpenAI-compatible (OpenAI/GLM/OpenRouter/Ollama)',
        };
    }
}
