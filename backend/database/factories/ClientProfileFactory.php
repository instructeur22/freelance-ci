<?php

namespace Database\Factories;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientProfileFactory extends Factory
{
    protected $model = ClientProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => fake()->company(),
            'company_website' => fake()->url(),
            'company_size' => fake()->randomElement(['1-10', '11-50', '51-200', '200+']),
            'industry' => fake()->word(),
            'total_projects_posted' => 0,
            'total_spent' => 0,
            'average_rating' => 0,
        ];
    }
}
