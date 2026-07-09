<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * §8.1 — driver OpenAI-compatible (OpenAI/GLM/OpenRouter/Ollama).
 *
 * Nota json_object (§8.1): sesetengah endpoint menolak response_format. Jika
 * ditolak → ulang TANPA medan itu. OpenAI juga menolak json_object jika perkataan
 * "json" TIADA dalam mesej — system prompt §8.3 memang mengandungi "JSON".
 * TIADA retry rangkaian di lapisan ini (retry di Job).
 */
class OpenAiCompatibleClient implements AiClient
{
    public function complete(string $system, string $user, AiProvider $cfg): AiResult
    {
        $base = rtrim($cfg->base_url ?: 'https://api.openai.com/v1', '/');
        $timeout = $cfg->timeout_s ?: 90;

        $payload = [
            'model' => $cfg->model,
            'max_tokens' => $cfg->max_tokens,
            'temperature' => (float) $cfg->temperature,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        $response = $this->send($base, $timeout, $cfg->api_key, $payload);

        // Jika ditolak (kemungkinan kerana response_format) → ulang tanpa medan itu.
        if ($response->failed()) {
            unset($payload['response_format']);
            $response = $this->send($base, $timeout, $cfg->api_key, $payload);
        }

        if ($response->failed()) {
            throw new AiException('OpenAI-compatible HTTP '.$response->status().': '.$response->body());
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content)) {
            throw new AiException('Respons tidak mengandungi kandungan teks.');
        }

        return new AiResult(
            content: $content,
            tokensIn: (int) $response->json('usage.prompt_tokens', 0),
            tokensOut: (int) $response->json('usage.completion_tokens', 0),
        );
    }

    private function send(string $base, int $timeout, string $apiKey, array $payload): Response
    {
        try {
            return Http::timeout($timeout)
                ->withToken($apiKey)
                ->post($base.'/chat/completions', $payload);
        } catch (Throwable $e) {
            throw new AiException('Panggilan OpenAI-compatible gagal: '.$e->getMessage(), previous: $e);
        }
    }
}
