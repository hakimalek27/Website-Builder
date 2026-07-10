<?php

use App\Models\AiProvider;
use App\Services\Ai\AnthropicClient;
use App\Services\Ai\OpenAiCompatibleClient;
use Illuminate\Support\Facades\Http;

// §Fasa 13 W1 — opsyen AiClient::complete (json / max_tokens).

function openAiOkResponse(): array
{
    return [
        'choices' => [['message' => ['content' => 'hasil']]],
        'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20],
    ];
}

it('omits response_format when the json option is false', function () {
    Http::fake(['*' => Http::response(openAiOkResponse())]);
    $cfg = AiProvider::factory()->openAiCompatible()->make(['model' => 'gpt-4o']);

    (new OpenAiCompatibleClient)->complete('sys', 'usr', $cfg, ['json' => false]);

    Http::assertSent(fn ($req) => ! isset($req->data()['response_format']));
});

it('includes response_format by default (JSON output)', function () {
    Http::fake(['*' => Http::response(openAiOkResponse())]);
    $cfg = AiProvider::factory()->openAiCompatible()->make(['model' => 'gpt-4o']);

    (new OpenAiCompatibleClient)->complete('sys', 'usr', $cfg);

    Http::assertSent(fn ($req) => ($req->data()['response_format']['type'] ?? null) === 'json_object');
});

it('overrides max_tokens for a classic model', function () {
    Http::fake(['*' => Http::response(openAiOkResponse())]);
    $cfg = AiProvider::factory()->openAiCompatible()->make(['model' => 'gpt-4o', 'max_tokens' => 3000]);

    (new OpenAiCompatibleClient)->complete('sys', 'usr', $cfg, ['max_tokens' => 30000]);

    Http::assertSent(fn ($req) => ($req->data()['max_tokens'] ?? null) === 30000);
});

it('overrides max_completion_tokens for a modern gpt-5 model without response_format', function () {
    Http::fake(['*' => Http::response(openAiOkResponse())]);
    $cfg = AiProvider::factory()->openAiCompatible()->make(['model' => 'gpt-5.5', 'max_tokens' => 3000]);

    (new OpenAiCompatibleClient)->complete('sys', 'usr', $cfg, ['max_tokens' => 30000, 'json' => false]);

    Http::assertSent(fn ($req) => ($req->data()['max_completion_tokens'] ?? null) === 30000
        && ! isset($req->data()['max_tokens'])
        && ! isset($req->data()['response_format']));
});

it('overrides max_tokens for the Anthropic client', function () {
    Http::fake(['*api.anthropic.com*' => Http::response([
        'content' => [['type' => 'text', 'text' => 'ok']],
        'usage' => ['input_tokens' => 5, 'output_tokens' => 6],
    ])]);
    $cfg = AiProvider::factory()->make(['model' => 'claude-sonnet-5', 'max_tokens' => 3000]);

    (new AnthropicClient)->complete('sys', 'usr', $cfg, ['max_tokens' => 12345]);

    Http::assertSent(fn ($req) => str_contains($req->url(), 'anthropic') && ($req->data()['max_tokens'] ?? null) === 12345);
});
