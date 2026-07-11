<?php

namespace Database\Factories;

use App\Models\TemplateCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemplateCatalog>
 */
class TemplateCatalogFactory extends Factory
{
    protected $model = TemplateCatalog::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true).' Theme',
            'source' => 'themeforest',
            'url' => 'https://themeforest.net/item/'.$this->faker->slug().'/'.$this->faker->numberBetween(10000000, 99999999),
            'demo_url' => null,
            'categories' => ['masjid'],
            'style_tags' => ['moden'],
            'thumbnail_path' => null,
            'screenshots' => null,
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
