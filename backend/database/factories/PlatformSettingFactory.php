<?php

namespace Database\Factories;

use App\Models\PlatformSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlatformSettingFactory extends Factory
{
    protected $model = PlatformSetting::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'value' => fake()->word(),
            'group' => fake()->randomElement(['general', 'payment', 'notification']),
            'type' => 'string',
            'description' => fake()->sentence(),
            'is_public' => false,
        ];
    }
}
