<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectPage>
 */
class ProjectPageFactory extends Factory
{
    protected $model = ProjectPage::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'page_key' => 'utama',
            'enabled' => true,
            'custom_name' => null,
            'sort' => 0,
        ];
    }
}
