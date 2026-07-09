<?php

namespace Database\Factories;

use App\Models\Approval;
use App\Models\Generation;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Approval>
 */
class ApprovalFactory extends Factory
{
    protected $model = Approval::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'generation_id' => Generation::factory(),
            'snapshot' => ['reka_spec_version' => '1.0'],
            'pic_name' => fake()->name(),
            'pic_position' => 'Setiausaha AJK',
            'pic_phone' => '01'.fake()->numerify('########'),
            'consent_pdpa' => true,
            'declare_authority' => true,
            'ip' => fake()->ipv4(),
            'user_agent' => 'PHPUnit',
            'approved_at' => now(),
        ];
    }
}
