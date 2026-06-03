<?php

namespace Database\Factories;

use App\Models\FreelanceProfile;
use App\Models\Verification;
use App\Models\VerifiedBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

class VerifiedBadgeFactory extends Factory
{
    protected $model = VerifiedBadge::class;

    public function definition(): array
    {
        return [
            'freelance_profile_id' => FreelanceProfile::factory(),
            'verification_id' => Verification::factory(),
            'badge_type' => 'verified',
            'is_active' => true,
            'granted_at' => now(),
            'expires_at' => fake()->dateTimeBetween('+6 months', '+1 year'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'expires_at' => now()->subDay(),
        ]);
    }
}
