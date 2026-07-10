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
    public function complete(string $system, string $user, AiProvider $cfg, array $options = []): AiResult
    {
        $base = rtrim($cfg->base_url ?: 'https://api.openai.com/v1', '/');
        $timeout = $cfg->timeout_s ?: 90;

        $modern = $this->usesCompletionTokenParam((string) $cfg->model);
        $maxTokens = (int) ($options['max_tokens'] ?? $cfg->max_tokens);
        $wantsJson = ($options['json'] ?? true) !== false;

        $payload = [
            'model' => $cfg->model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ];
        // Saluran HTML (§Fasa 13) minta teks bebas → jangan paksa json_object.
        if ($wantsJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }
        // gpt-5.x & siri reasoning (o1/o3/o4) guna 'max_completion_tokens' + hanya temperature lalai.
        $payload[$modern ? 'max_completion_tokens' : 'max_tokens'] = $maxTokens;
        if (! $modern) {
            $payload['temperature'] = (float) $cfg->temperature;
        }

        $response = $this->send($base, $timeout, $cfg->api_key, $payload);

        // Fallback adaptif: buang/tukar medan yang ditolak model/endpoint (maks 3 pusingan) —
        // cth 'max_tokens'→'max_completion_tokens', temperature tak disokong, response_format ditolak.
        for ($i = 0; $i < 3 && $response->failed(); $i++) {
            $adapted = $this->adaptPayload($payload, (string) $response->body());
            if ($adapted === $payload) {
                break;
            }
            $payload = $adapted;
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
            finishReason: $this->normalizeFinish($response->json('choices.0.finish_reason')),
        );
    }

    /** Normalkan finish_reason OpenAI-compatible ('length' = terpotong). Kosong → null. */
    private function normalizeFinish(mixed $reason): ?string
    {
        return is_string($reason) && $reason !== '' ? $reason : null;
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

    /** Model OpenAI gpt-5.x & siri reasoning (o1/o3/o4) — guna 'max_completion_tokens' + temperature lalai. */
    private function usesCompletionTokenParam(string $model): bool
    {
        return (bool) preg_match('/\b(gpt-5|o[1-4])\b/i', $model);
    }

    /** Sesuaikan payload ikut mesej ralat 400 (medan tak disokong oleh model/endpoint). */
    private function adaptPayload(array $payload, string $errorBody): array
    {
        if (str_contains($errorBody, 'max_completion_tokens') && array_key_exists('max_tokens', $payload)) {
            $payload['max_completion_tokens'] = $payload['max_tokens'];
            unset($payload['max_tokens']);
        }
        // Nilai token melebihi had model → turunkan ke had yang dilaporkan ("...at most N...").
        if (preg_match('/at most ([0-9]+)/i', $errorBody, $m)) {
            $cap = (int) $m[1];
            foreach (['max_completion_tokens', 'max_tokens'] as $k) {
                if (isset($payload[$k]) && (int) $payload[$k] > $cap) {
                    $payload[$k] = $cap;
                }
            }
        }
        if (str_contains($errorBody, 'temperature') && array_key_exists('temperature', $payload)) {
            unset($payload['temperature']);
        }
        if (str_contains($errorBody, 'response_format') && array_key_exists('response_format', $payload)) {
            unset($payload['response_format']);
        }

        return $payload;
    }
}
