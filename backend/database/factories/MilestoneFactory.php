<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Milestone;
use Illuminate\Database\Eloquent\Factories\Factory;

class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'amount' => fake()->randomFloat(2, 10000, 100000),
            'due_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'is_completed' => false,
            'completed_at' => null,
            'sort_order' => fake()->numberBetween(1, 10),
            'delivered_at' => null,
            'validated_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_completed' => true,
            'completed_at' => now()->subDays(3),
            'delivered_at' => now()->subDays(2),
        ]);
    }
}
