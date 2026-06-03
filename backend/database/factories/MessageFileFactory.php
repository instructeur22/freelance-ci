<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\MessageFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFileFactory extends Factory
{
    protected $model = MessageFile::class;

    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'file_url' => fake()->url(),
            'file_name' => fake()->word() . '.' . fake()->fileExtension(),
            'file_type' => fake()->randomElement(['image/jpeg', 'application/pdf', 'text/plain']),
            'file_size' => fake()->numberBetween(1000, 5000000),
        ];
    }
}
