<?php

namespace App\Services\Ai;

// Hasil panggilan AI (§8.1).
final class AiResult
{
    public function __construct(
        public string $content,
        public int $tokensIn,
        public int $tokensOut,
        // §Fasa 14 — sebab model berhenti (dinormalkan): 'length' = output terpotong
        // (had token dicapai), 'stop' = selesai normal, null jika endpoint tak lapor.
        public ?string $finishReason = null,
    ) {}
}
