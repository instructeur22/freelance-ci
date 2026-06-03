<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Conversation;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'contract_id' => null,
            'subject' => fake()->sentence(3),
            'last_message_at' => null,
        ];
    }
}
