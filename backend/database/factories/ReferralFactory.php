<?php

namespace Database\Factories;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_id' => User::factory(),
            'referral_code_id' => ReferralCode::factory(),
            'status' => ReferralStatus::Pending,
            'reward_amount' => 0,
            'paid_at' => null,
        ];
    }

    public function rewarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReferralStatus::Paid,
            'reward_amount' => 5000,
            'paid_at' => now(),
        ]);
    }
}
