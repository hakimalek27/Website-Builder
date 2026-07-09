<?php

use App\Enums\AiDriver;
use App\Enums\AiVendor;
use App\Models\AiProvider;
use App\Services\Ai\AiClientFactory;
use Illuminate\Support\Facades\Http;

// §8.1 — setiap penyedia pratetap mesti POST ke endpoint yang betul (elak 404 base URL salah).

it('posts to the correct endpoint for every vendor preset', function (AiVendor $vendor) {
    $base = rtrim($vendor->baseUrl() ?: 'https://api.openai.com/v1', '/');
    $isAnthropic = $vendor->driver() === AiDriver::Anthropic;
    $expected = $base.($isAnthropic ? '/v1/messages' : '/chat/completions');

    Http::fake([
        '*' => Http::response($isAnthropic
            ? ['content' => [['type' => 'text', 'text' => 'OK']], 'usage' => ['input_tokens' => 1, 'output_tokens' => 1]]
            : ['choices' => [['message' => ['content' => 'OK']]], 'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1]]),
    ]);

    $provider = AiProvider::factory()->create([
        'vendor' => $vendor,
        'driver' => $vendor->driver(),
        'base_url' => $vendor->baseUrl() ?: null,
        'model' => $vendor->models()[0] ?? 'test-model',
    ]);

    $result = app(AiClientFactory::class)->for($provider)->complete('sys', 'user', $provider);

    expect($result->content)->toBe('OK');
    Http::assertSent(fn ($request) => $request->url() === $expected);
})->with(array_map(fn (AiVendor $v) => [$v], AiVendor::cases()));

it('maps each vendor to the expected base URL', function () {
    expect(AiVendor::OpenRouter->baseUrl())->toBe('https://openrouter.ai/api/v1')
        ->and(AiVendor::DeepSeek->baseUrl())->toBe('https://api.deepseek.com')
        ->and(AiVendor::Zhipu->baseUrl())->toBe('https://api.z.ai/api/paas/v4')
        ->and(AiVendor::Anthropic->driver())->toBe(AiDriver::Anthropic)
        ->and(AiVendor::OpenRouter->driver())->toBe(AiDriver::OpenAiCompatible);
});
