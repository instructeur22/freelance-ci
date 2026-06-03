<?php

namespace Database\Factories;

use App\Models\FreelanceProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelanceProfileFactory extends Factory
{
    protected $model = FreelanceProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'professional_title' => fake()->jobTitle(),
            'experience_level' => fake()->randomElement(['debutant', 'intermediaire', 'confirme', 'expert']),
            'years_experience' => fake()->numberBetween(0, 20),
            'education_level' => fake()->randomElement(['bac', 'bac+2', 'bac+3', 'bac+5', 'master', 'doctorat']),
            'hourly_rate_min' => (string) fake()->numberBetween(1000, 5000),
            'hourly_rate_max' => (string) fake()->numberBetween(5000, 50000),
            'currency' => 'XOF',
            'is_available' => true,
            'total_projects_completed' => 0,
            'total_projects_in_progress' => 0,
            'average_rating' => 0,
            'total_reviews' => 0,
            'total_earnings' => 0,
            'success_rate' => 0,
            'last_active_at' => null,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function experienced(): static
    {
        return $this->state(fn (array $attributes) => [
            'years_experience' => fake()->numberBetween(5, 20),
            'experience_level' => 'expert',
            'total_projects_completed' => fake()->numberBetween(20, 100),
            'success_rate' => fake()->randomFloat(2, 80, 100),
        ]);
    }
}
