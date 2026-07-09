<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectSection>
 */
class ProjectSectionFactory extends Factory
{
    protected $model = ProjectSection::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'section_key' => 'step_1',
            'data' => [],
        ];
    }
}
