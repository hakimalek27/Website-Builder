<?php

namespace App\Services\Ai;

// Hasil panggilan AI (§8.1).
final class AiResult
{
    public function __construct(
        public string $content,
        public int $tokensIn,
        public int $tokensOut,
    ) {}
}
