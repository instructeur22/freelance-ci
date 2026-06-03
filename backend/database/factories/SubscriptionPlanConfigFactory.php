<?php

namespace Database\Factories;

use App\Enums\SubscriptionPlan;
use App\Models\SubscriptionPlanConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanConfigFactory extends Factory
{
    protected $model = SubscriptionPlanConfig::class;

    public function definition(): array
    {
        return [
            'plan' => fake()->randomElement(SubscriptionPlan::cases()),
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'price_monthly' => fake()->randomFloat(2, 0, 15000),
            'price_yearly' => fake()->randomFloat(2, 0, 150000),
            'max_projects' => fake()->numberBetween(1, 50),
            'max_quotes_per_month' => fake()->numberBetween(5, 100),
            'has_verified_badge' => false,
            'has_boost_option' => false,
            'features' => ['feature_1' => true, 'feature_2' => false],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function forPlan(SubscriptionPlan $plan): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => $plan,
        ]);
    }

    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => SubscriptionPlan::Starter,
            'name' => 'Starter',
            'price_monthly' => 0,
            'price_yearly' => 0,
            'max_projects' => 3,
            'max_quotes_per_month' => 5,
            'has_verified_badge' => false,
            'has_boost_option' => false,
            'sort_order' => 1,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => SubscriptionPlan::Pro,
            'name' => 'Pro',
            'price_monthly' => 5000,
            'price_yearly' => 50000,
            'max_projects' => 20,
            'max_quotes_per_month' => 30,
            'has_verified_badge' => true,
            'has_boost_option' => true,
            'sort_order' => 2,
        ]);
    }

    public function expert(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => SubscriptionPlan::Expert,
            'name' => 'Expert',
            'price_monthly' => 15000,
            'price_yearly' => 150000,
            'max_projects' => 999,
            'max_quotes_per_month' => 999,
            'has_verified_badge' => true,
            'has_boost_option' => true,
            'sort_order' => 3,
        ]);
    }
}
