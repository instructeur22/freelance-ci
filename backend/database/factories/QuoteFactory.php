<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'freelance_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 50000, 500000),
            'currency' => 'XOF',
            'estimated_duration' => fake()->numberBetween(5, 60),
            'proposal' => fake()->paragraphs(2, true),
            'status' => QuoteStatus::Pending,
            'read_at' => null,
            'responded_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuoteStatus::Accepted,
            'responded_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuoteStatus::Refused,
            'responded_at' => now(),
        ]);
    }
}
