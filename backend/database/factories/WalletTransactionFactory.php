<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        $balanceBefore = fake()->randomFloat(2, 0, 100000);
        $amount = fake()->randomFloat(2, 1000, 50000);

        return [
            'wallet_id' => Wallet::factory(),
            'type' => fake()->randomElement(['credit', 'debit']),
            'direction' => fake()->randomElement(['credit', 'debit']),
            'amount_xof' => $amount,
            'balance_before_xof' => $balanceBefore,
            'balance_after_xof' => $balanceBefore + $amount,
            'description' => fake()->sentence(),
            'reference' => fake()->uuid(),
            'metadata' => ['source' => fake()->word()],
        ];
    }
}
