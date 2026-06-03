<?php

namespace Database\Factories;

use App\Enums\EscrowStatus;
use App\Models\Contract;
use App\Models\Escrow;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EscrowFactory extends Factory
{
    protected $model = Escrow::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 50000, 500000);

        return [
            'contract_id' => Contract::factory(),
            'payment_id' => Payment::factory(),
            'status' => EscrowStatus::Holding,
            'amount' => $amount,
            'held_amount' => $amount,
            'released_amount' => 0,
            'refunded_amount' => 0,
            'held_at' => now(),
            'released_at' => null,
            'refunded_at' => null,
        ];
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EscrowStatus::Released,
            'released_amount' => $attributes['amount'] ?? 0,
            'released_at' => now(),
        ]);
    }
}
