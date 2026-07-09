<?php

namespace App\Services\Ai;

use App\Models\AiProvider;

// §8.1 — abstraksi provider AI.
interface AiClient
{
    /**
     * @throws AiException
     */
    public function complete(string $system, string $user, AiProvider $cfg): AiResult;
}
