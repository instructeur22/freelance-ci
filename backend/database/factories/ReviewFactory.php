<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraph(),
            'criteria_ratings' => [
                'quality' => fake()->numberBetween(1, 5),
                'communication' => fake()->numberBetween(1, 5),
                'deadline' => fake()->numberBetween(1, 5),
            ],
            'is_flagged' => false,
        ];
    }

    public function flagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_flagged' => true,
        ]);
    }
}
