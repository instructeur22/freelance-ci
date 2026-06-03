<?php

namespace Database\Factories;

use App\Enums\GenderType;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => fake()->paragraph(),
            'title' => fake()->jobTitle(),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'address' => fake()->streetAddress(),
            'gender' => fake()->randomElement(GenderType::cases()),
            'birth_date' => fake()->date(max: '-18 years'),
            'website_url' => fake()->url(),
            'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
            'github_url' => 'https://github.com/' . fake()->userName(),
            'phone_secondary' => fake()->phoneNumber(),
            'is_visible' => true,
        ];
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }
}
