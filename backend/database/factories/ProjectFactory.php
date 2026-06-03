<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\JobCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'client_id' => User::factory()->client(),
            'category_id' => JobCategory::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'status' => ProjectStatus::Open,
            'budget_min' => fake()->numberBetween(10000, 100000),
            'budget_max' => fake()->numberBetween(100000, 1000000),
            'currency' => 'XOF',
            'experience_level' => fake()->randomElement(['debutant', 'intermediaire', 'confirme']),
            'duration_days' => fake()->numberBetween(7, 90),
            'required_skills' => [fake()->word(), fake()->word()],
            'project_type' => fake()->randomElement(['fixed', 'hourly']),
            'is_featured' => false,
            'is_urgent' => false,
            'is_remote' => true,
            'location' => fake()->city(),
            'quotes_count' => 0,
            'views_count' => 0,
            'published_at' => now(),
            'deadline_at' => fake()->dateTimeBetween('+1 week', '+3 months'),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Cancelled,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::InProgress,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_urgent' => true,
        ]);
    }
}
