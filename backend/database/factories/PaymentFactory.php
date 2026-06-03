<?php

namespace Database\Factories;

use App\Enums\PaymentChannel;
use App\Enums\PaymentOperator;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 10000, 500000);

        return [
            'contract_id' => Contract::factory(),
            'legacy_user_id' => User::factory(),
            'transaction_id' => Transaction::factory(),
            'amount' => $amount,
            'platform_fee' => $amount * 0.05,
            'net_amount_xof' => $amount * 0.95,
            'currency' => 'XOF',
            'status' => PaymentStatus::Pending,
            'legacy_channel' => fake()->randomElement(PaymentChannel::cases()),
            'legacy_operator' => fake()->randomElement(PaymentOperator::cases()),
            'transaction_type' => TransactionType::Mission,
            'reference' => 'PAY-' . fake()->uuid(),
            'description' => fake()->sentence(),
            'metadata' => ['source' => 'test'],
            'paid_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Released,
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Failed,
        ]);
    }
}
