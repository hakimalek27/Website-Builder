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

// §Fasa 14 W2 — AiResult dedah finish_reason (dinormalkan) untuk kesan terpotong.

it('exposes OpenAI finish_reason length as-is', function () {
    Http::fake(['*' => Http::response([
        'choices' => [['message' => ['content' => 'hasil'], 'finish_reason' => 'length']],
        'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20],
    ])]);
    $cfg = AiProvider::factory()->openAiCompatible()->make(['model' => 'gpt-4o']);

    $res = (new OpenAiCompatibleClient)->complete('sys', 'usr', $cfg, ['json' => false]);

    expect($res->finishReason)->toBe('length');
});

it('passes through OpenAI finish_reason stop and null when absent', function () {
    Http::fake(['stop.test/*' => Http::response([
        'choices' => [['message' => ['content' => 'a'], 'finish_reason' => 'stop']],
        'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
    ]), 'none.test/*' => Http::response([
        'choices' => [['message' => ['content' => 'b']]],
        'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
    ])]);
    $stop = AiProvider::factory()->openAiCompatible()->make(['model' => 'gpt-4o', 'base_url' => 'https://stop.test/v1']);
    $none = AiProvider::factory()->openAiCompatible()->make(['model' => 'gpt-4o', 'base_url' => 'https://none.test/v1']);

    expect((new OpenAiCompatibleClient)->complete('s', 'u', $stop, ['json' => false])->finishReason)->toBe('stop');
    expect((new OpenAiCompatibleClient)->complete('s', 'u', $none, ['json' => false])->finishReason)->toBeNull();
});

it('normalizes Anthropic stop_reason max_tokens to length', function () {
    Http::fake(['*api.anthropic.com*' => Http::response([
        'content' => [['type' => 'text', 'text' => 'ok']],
        'stop_reason' => 'max_tokens',
        'usage' => ['input_tokens' => 5, 'output_tokens' => 6],
    ])]);
    $cfg = AiProvider::factory()->make(['model' => 'claude-sonnet-5']);

    expect((new AnthropicClient)->complete('s', 'u', $cfg)->finishReason)->toBe('length');
});

it('passes through Anthropic end_turn stop_reason', function () {
    Http::fake(['*api.anthropic.com*' => Http::response([
        'content' => [['type' => 'text', 'text' => 'ok']],
        'stop_reason' => 'end_turn',
        'usage' => ['input_tokens' => 5, 'output_tokens' => 6],
    ])]);
    $cfg = AiProvider::factory()->make(['model' => 'claude-sonnet-5']);

    expect((new AnthropicClient)->complete('s', 'u', $cfg)->finishReason)->toBe('end_turn');
});
