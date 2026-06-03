<?php

namespace Database\Factories;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminLogFactory extends Factory
{
    protected $model = AdminLog::class;

    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->admin(),
            'action' => fake()->word(),
            'entity_type' => fake()->word(),
            'entity_id' => (string) \Illuminate\Support\Str::uuid(),
            'old_values' => ['status' => 'old'],
            'new_values' => ['status' => 'new'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
