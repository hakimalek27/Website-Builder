<?php

namespace Database\Factories;

use App\Enums\AiDriver;
use App\Models\AiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AiProvider>
 */
class AiProviderFactory extends Factory
{
    protected $model = AiProvider::class;

    public function definition(): array
    {
        return [
            'name' => 'Provider '.fake()->unique()->word(),
            'driver' => AiDriver::Anthropic,
            'base_url' => null,
            'api_key' => 'sk-test-'.Str::random(24),
            'model' => 'claude-sonnet-5',
            'max_tokens' => 3000,
            'temperature' => 0.7,
            'timeout_s' => 90,
            'meta' => ['rate_in_per_mtok' => 3.0, 'rate_out_per_mtok' => 15.0, 'currency' => 'USD'],
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }

    public function openAiCompatible(): static
    {
        return $this->state(fn () => [
            'driver' => AiDriver::OpenAiCompatible,
            'base_url' => 'https://api.openai.example/v1',
            'model' => 'gpt-4o',
        ]);
    }
}
