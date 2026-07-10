<?php

namespace App\Services\Ai;

use App\Models\AiProvider;

// §8.1 — abstraksi provider AI.
interface AiClient
{
    /**
     * @param  array{json?: bool, max_tokens?: int}  $options
     *                                                         - json (lalai true): minta output JSON (OpenAI response_format json_object). Set false untuk output teks/HTML bebas (saluran HTML §Fasa 13).
     *                                                         - max_tokens: override had token provider untuk panggilan ini sahaja.
     *
     * @throws AiException
     */
    public function complete(string $system, string $user, AiProvider $cfg, array $options = []): AiResult;
}
