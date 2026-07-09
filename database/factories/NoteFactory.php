<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'author' => 'pic',
            'author_name' => fake()->name(),
            'kind' => 'general',
            'body' => fake()->sentence(),
        ];
    }

    public function fromAdmin(): static
    {
        return $this->state(fn () => ['author' => 'admin', 'author_name' => 'Azan']);
    }
}
