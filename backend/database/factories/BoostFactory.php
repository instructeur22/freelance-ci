<?php

namespace Database\Factories;

use App\Enums\BoostDuration;
use App\Enums\BoostTarget;
use App\Models\Boost;
use App\Models\FreelanceProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoostFactory extends Factory
{
    protected $model = Boost::class;

    public function definition(): array
    {
        return [
            'freelance_profile_id' => FreelanceProfile::factory(),
            'target' => BoostTarget::Profile,
            'target_id' => null,
            'duration' => BoostDuration::SevenDays,
            'amount_paid' => fake()->randomFloat(2, 3000, 15000),
            'payment_reference' => 'PAY-' . fake()->uuid(),
            'is_active' => true,
            'started_at' => now(),
            'ends_at' => fake()->dateTimeBetween('+7 days', '+30 days'),
        ];
    }

    public function projectBoost(): static
    {
        return $this->state(fn (array $attributes) => [
            'target' => BoostTarget::Project,
            'target_id' => (string) fake()->uuid(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'ends_at' => now()->subDay(),
        ]);
    }
}
