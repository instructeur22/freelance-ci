<?php

namespace Database\Factories;

use App\Models\FreelanceProfile;
use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioItemFactory extends Factory
{
    protected $model = PortfolioItem::class;

    public function definition(): array
    {
        return [
            'freelance_profile_id' => FreelanceProfile::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'project_url' => fake()->url(),
            'completed_date' => fake()->date(),
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
