<?php

namespace Database\Factories;

use App\Models\JobCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobCategoryFactory extends Factory
{
    protected $model = JobCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => $name,
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['code', 'design', 'writing', 'marketing', 'video']),
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'parent_id' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function childOf(JobCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
