<?php

namespace Database\Factories;

use App\Enums\GeniusPayStatus;
use App\Enums\PaymentChannel;
use App\Enums\PaymentOperator;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement([TransactionType::Mission, TransactionType::Subscription]),
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'currency' => 'XOF',
            'description' => fake()->sentence(),
            'payment_channel' => fake()->randomElement(PaymentChannel::cases()),
            'payment_operator' => fake()->randomElement(PaymentOperator::cases()),
            'operator_status' => GeniusPayStatus::PENDING,
            'operator_transaction_id' => 'gp-' . fake()->uuid(),
            'operator_reference' => 'ref-' . fake()->uuid(),
            'payment_url' => 'https://pay.geniuspay.com/tx/gp-' . fake()->uuid(),
            'metadata' => ['source' => 'test'],
            'paid_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'operator_status' => GeniusPayStatus::SUCCESS,
            'paid_at' => now(),
        ]);
    }
}
