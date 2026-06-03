<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'available_xof' => 0,
            'pending_xof' => 0,
            'total_earned_xof' => 0,
            'total_withdrawn_xof' => 0,
            'currency' => 'XOF',
        ];
    }

    public function withBalance(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'available_xof' => $amount,
            'total_earned_xof' => $amount,
        ]);
    }
}
