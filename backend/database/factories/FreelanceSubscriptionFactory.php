<?php

namespace Database\Factories;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\FreelanceProfile;
use App\Models\FreelanceSubscription;
use App\Models\SubscriptionPlanConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelanceSubscriptionFactory extends Factory
{
    protected $model = FreelanceSubscription::class;

    public function definition(): array
    {
        return [
            'freelance_profile_id' => FreelanceProfile::factory(),
            'plan_id' => SubscriptionPlanConfig::factory(),
            'status' => SubscriptionStatus::Active,
            'started_at' => now(),
            'ends_at' => fake()->dateTimeBetween('+1 month', '+1 year'),
            'trial_ends_at' => null,
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'amount_paid' => fake()->randomFloat(2, 0, 15000),
            'payment_reference' => 'PAY-' . fake()->uuid(),
            'auto_renew' => true,
            'cancelled_at' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Expired,
            'ends_at' => now()->subDay(),
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Trial,
            'trial_ends_at' => fake()->dateTimeBetween('+1 week', '+1 month'),
        ]);
    }
}
