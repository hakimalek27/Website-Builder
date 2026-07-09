<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'mosque_name' => 'Masjid '.fake()->unique()->words(2, true),
            'state' => 'Selangor',
            'pic_name' => fake()->name(),
            'pic_phone' => '01'.fake()->numerify('########'),
            'pic_email' => fake()->safeEmail(),
            'current_website' => null,
            'notes' => fake()->optional()->sentence(),
            'status' => LeadStatus::New,
        ];
    }
}
