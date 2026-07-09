<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'kind' => 'gallery',
            'path' => 'assets/'.Str::ulid().'.jpg',
            'original_name' => fake()->word().'.jpg',
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 2_000_000),
            'width' => 1600,
            'height' => 900,
            'sort' => 0,
        ];
    }
}
