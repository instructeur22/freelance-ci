<?php

namespace Database\Factories;

use App\Enums\DisputeStatus;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisputeFactory extends Factory
{
    protected $model = Dispute::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'raised_by' => User::factory(),
            'reason' => fake()->paragraph(),
            'evidence' => [['file' => fake()->url(), 'description' => fake()->sentence()]],
            'admin_notes' => null,
            'status' => DisputeStatus::Open,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DisputeStatus::Closed,
            'resolved_at' => now(),
        ]);
    }
}
