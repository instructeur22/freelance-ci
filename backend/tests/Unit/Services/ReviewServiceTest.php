<?php

namespace Tests\Unit\Services;

use App\Models\Contract;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReviewService $reviewService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewService = new ReviewService();
    }

    public function test_createReview_creates_review(): void
    {
        $freelance = User::factory()->create();
        $client = User::factory()->create();
        $contract = Contract::factory()->completed()->create([
            'client_id' => $client->id,
            'freelance_id' => $freelance->id,
        ]);

        $review = $this->reviewService->createReview($contract, $client, [
            'rating' => 4,
            'comment' => 'Great work!',
            'criteria_ratings' => ['quality' => 5, 'communication' => 4, 'deadline' => 3],
        ]);

        $this->assertInstanceOf(Review::class, $review);
        $this->assertEquals($contract->id, $review->contract_id);
        $this->assertEquals(4, $review->rating);
    }

    public function test_replyToReview_creates_reply(): void
    {
        $contract = Contract::factory()->completed()->create();
        $author = User::factory()->create();
        $review = Review::factory()->create([
            'contract_id' => $contract->id,
            'reviewee_id' => $author->id,
        ]);

        $reply = $this->reviewService->replyToReview($review, $author, 'Thank you!');

        $this->assertInstanceOf(Review::class, $reply);
        $this->assertNotNull($reply->reply);
        $this->assertEquals('Thank you!', $reply->reply->comment);
    }

    public function test_listForFreelance_returns_paginated_reviews(): void
    {
        $freelance = User::factory()->create();
        Review::factory()->count(3)->create(['reviewee_id' => $freelance->id]);

        $reviews = $this->reviewService->listForFreelance($freelance->id);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $reviews);
        $this->assertEquals(3, $reviews->total());
    }
}
