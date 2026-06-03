<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 50000, 500000);

        return [
            'contract_id' => Contract::factory(),
            'payment_id' => Payment::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->randomNumber(6),
            'total_xof' => $amount,
            'platform_fee' => $amount * 0.05,
            'net_amount' => $amount * 0.95,
            'currency' => 'XOF',
            'status' => 'pending',
            'issue_date' => now(),
            'due_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
            'paid_date' => null,
            'invoice_data' => ['items' => [['description' => fake()->sentence(), 'amount' => $amount]]],
        ];
    }
}
