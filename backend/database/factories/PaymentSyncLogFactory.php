<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentSyncLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentSyncLogFactory extends Factory
{
    protected $model = PaymentSyncLog::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'action' => fake()->word(),
            'status' => fake()->randomElement(['success', 'failed']),
            'request_data' => ['endpoint' => fake()->url()],
            'response_data' => ['status' => 'ok'],
            'error_message' => null,
        ];
    }
}
