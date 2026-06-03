<?php

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['google', 'facebook', 'github']),
            'provider_id' => (string) fake()->unique()->randomNumber(9),
            'provider_token' => fake()->sha256(),
            'provider_refresh_token' => fake()->sha256(),
        ];
    }
}
