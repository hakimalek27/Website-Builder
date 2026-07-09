<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'mosque_name' => 'Masjid '.fake()->unique()->words(2, true),
            'short_name' => null,
            'tier' => Tier::MasjidKariah,
            'is_gov' => false,
            'state' => 'Selangor',
            'jakim_zone' => 'SGR01',
            'status' => ProjectStatus::Invited,
            'quota_ai_total' => 3,
            'quota_ai_used' => 0,
            'quota_design_used' => 0,
        ];
    }

    public function status(ProjectStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function submitted(): static
    {
        return $this->state(fn () => [
            'status' => ProjectStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }
}
