<?php

namespace Database\Factories;

use App\Enums\GenerationStatus;
use App\Enums\GenerationType;
use App\Models\Generation;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Generation>
 */
class GenerationFactory extends Factory
{
    protected $model = Generation::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'ai_provider_id' => null,
            'type' => GenerationType::Initial,
            'status' => GenerationStatus::Queued,
            'progress_step' => 0,
            'tokens_in' => 0,
            'tokens_out' => 0,
            'cost_estimate' => 0,
            'attempt' => 0,
            'created_by' => 'pic',
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn () => [
            'status' => GenerationStatus::Succeeded,
            'progress_step' => 4,
            'finished_at' => now(),
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => GenerationStatus::Processing]);
    }
}
