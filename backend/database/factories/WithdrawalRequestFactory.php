<?php

namespace Database\Factories;

use App\Enums\WithdrawalMethod;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawalRequestFactory extends Factory
{
    protected $model = WithdrawalRequest::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 5000, 100000);

        return [
            'wallet_id' => Wallet::factory(),
            'user_id' => User::factory(),
            'amount' => $amount,
            'fee' => $amount * 0.02,
            'net_amount' => $amount * 0.98,
            'withdrawal_method' => fake()->randomElement(WithdrawalMethod::cases()),
            'account_identifier' => fake()->phoneNumber(),
            'status' => 'pending',
            'admin_notes' => null,
            'processed_by' => null,
            'processed_at' => null,
        ];
    }
}
