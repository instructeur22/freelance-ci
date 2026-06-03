<?php

namespace Database\Factories;

use App\Models\GeniusPayWebhook;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeniusPayWebhookFactory extends Factory
{
    protected $model = GeniusPayWebhook::class;

    public function definition(): array
    {
        return [
            'event_type' => fake()->randomElement(['payment.success', 'payment.failed']),
            'raw_payload' => ['transaction_id' => 'gp-' . fake()->uuid(), 'status' => 'SUCCESS'],
            'signature' => fake()->sha256(),
            'is_processed' => false,
            'processed_at' => null,
            'error_message' => null,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_processed' => true,
            'processed_at' => now(),
        ]);
    }
}
