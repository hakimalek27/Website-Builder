<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'token_hash' => Invitation::hashToken(Invitation::generateToken()),
            'pic_name' => fake()->name(),
            'pic_phone' => '01'.fake()->numerify('########'),
            'pic_email' => fake()->safeEmail(),
            'expires_at' => now()->addDays(30),
            'opens_count' => 0,
        ];
    }

    /** Cipta jemputan dengan token plaintext diketahui (untuk ujian). */
    public function withToken(string $token): static
    {
        return $this->state(fn () => ['token_hash' => Invitation::hashToken($token)]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['expires_at' => now()->subDay()]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => ['revoked_at' => now()]);
    }
}
