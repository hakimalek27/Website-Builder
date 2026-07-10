<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * §8.1 — driver Anthropic. TIADA retry di lapisan client (retry di Job).
 */
class AnthropicClient implements AiClient
{
    public function complete(string $system, string $user, AiProvider $cfg, array $options = []): AiResult
    {
        $base = rtrim($cfg->base_url ?: 'https://api.anthropic.com', '/');
        // 'json' diabaikan (Anthropic tiada response_format); max_tokens boleh di-override (§Fasa 13).
        $maxTokens = (int) ($options['max_tokens'] ?? $cfg->max_tokens);

        try {
            $response = Http::timeout($cfg->timeout_s ?: 90)
                ->withHeaders([
                    'x-api-key' => $cfg->api_key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($base.'/v1/messages', [
                    'model' => $cfg->model,
                    'max_tokens' => $maxTokens,
                    'system' => $system,
                    'messages' => [
                        ['role' => 'user', 'content' => $user],
                    ],
                ]);
        } catch (Throwable $e) {
            throw new AiException('Panggilan Anthropic gagal: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new AiException('Anthropic HTTP '.$response->status().': '.$response->body());
        }

        $content = $response->json('content.0.text');
        if (! is_string($content)) {
            throw new AiException('Respons Anthropic tidak mengandungi kandungan teks.');
        }

        return new AiResult(
            content: $content,
            tokensIn: (int) $response->json('usage.input_tokens', 0),
            tokensOut: (int) $response->json('usage.output_tokens', 0),
            finishReason: $this->normalizeFinish($response->json('stop_reason')),
        );
    }

    /** Normalkan stop_reason Anthropic → sentinel sama seperti OpenAI ('max_tokens'→'length'). */
    private function normalizeFinish(mixed $reason): ?string
    {
        if (! is_string($reason) || $reason === '') {
            return null;
        }

        return $reason === 'max_tokens' ? 'length' : $reason;
    }
}
