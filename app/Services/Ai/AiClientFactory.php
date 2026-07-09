<?php

namespace App\Services\Ai;

use App\Enums\AiDriver;
use App\Models\AiProvider;

// Selesaikan driver dari konfigurasi provider (§8.1).
class AiClientFactory
{
    public function for(AiProvider $provider): AiClient
    {
        return match ($provider->driver) {
            AiDriver::Anthropic => new AnthropicClient,
            AiDriver::OpenAiCompatible => new OpenAiCompatibleClient,
        };
    }
}
