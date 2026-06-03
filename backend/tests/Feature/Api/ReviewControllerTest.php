<?php

namespace Tests\Feature\Api;

use App\Models\Contract;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_review(): void
    {
        $reviewer = User::factory()->create();
        $freelance = User::factory()->create();
        $contract = Contract::factory()->completed()->create([
            'client_id' => $reviewer->id,
            'freelance_id' => $freelance->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($reviewer))
            ->postJson('/api/contracts/' . $contract->id . '/review', [
                'rating' => 4,
                'comment' => 'Great work!',
                'criteria_ratings' => [
                    'quality' => 5,
                    'communication' => 4,
                    'deadline' => 3,
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_freelance_reviews(): void
    {
        $freelance = User::factory()->create();
        $reviewer = User::factory()->create();
        Review::factory()->count(2)->create([
            'reviewee_id' => $freelance->id,
            'reviewer_id' => $reviewer->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($reviewer))
            ->getJson('/api/freelances/' . $freelance->id . '/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_reply_to_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['reviewee_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/reviews/' . $review->id . '/reply', [
                'comment' => 'Thank you!',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
