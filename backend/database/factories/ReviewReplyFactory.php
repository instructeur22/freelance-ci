<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewReplyFactory extends Factory
{
    protected $model = ReviewReply::class;

    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->paragraph(),
        ];
    }
}
