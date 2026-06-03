<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Enums\VerificationType;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Factories\Factory;

class VerificationFactory extends Factory
{
    protected $model = Verification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(VerificationType::cases()),
            'document_url' => fake()->url(),
            'status' => VerificationStatus::Pending,
            'admin_notes' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'expires_at' => fake()->dateTimeBetween('+1 year', '+2 years'),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VerificationStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VerificationStatus::Rejected,
            'admin_notes' => fake()->sentence(),
            'reviewed_at' => now(),
        ]);
    }
}
