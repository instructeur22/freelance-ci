<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'quote_id' => Quote::factory(),
            'client_id' => User::factory()->client(),
            'freelance_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraphs(2, true),
            'total_amount' => fake()->randomFloat(2, 50000, 500000),
            'currency' => 'XOF',
            'platform_fee' => fake()->randomFloat(2, 1000, 25000),
            'freelance_amount' => fake()->randomFloat(2, 40000, 475000),
            'start_date' => fake()->dateTimeBetween('now', '+1 week'),
            'end_date' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'terms_conditions' => fake()->paragraph(),
            'status' => ContractStatus::Draft,
            'client_signed_at' => null,
            'freelance_signed_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::Signed,
            'client_signed_at' => now(),
            'freelance_signed_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::Completed,
            'client_signed_at' => now()->subDays(30),
            'freelance_signed_at' => now()->subDays(30),
        ]);
    }
}
