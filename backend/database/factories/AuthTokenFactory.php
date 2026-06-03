<?php

namespace Database\Factories;

use App\Models\AuthToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthTokenFactory extends Factory
{
    protected $model = AuthToken::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'device_name' => fake()->word(),
            'device_type' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'last_used_at' => null,
            'expires_at' => fake()->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
