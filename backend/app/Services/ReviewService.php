<?php
namespace App\Services;

use App\Models\Contract;
use App\Models\FreelanceProfile;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function create(User $user, string $contractId, array $data): ?Review
    {
        $contract = Contract::find($contractId);
        if (!$contract) return null;

        try {
            return $this->createReview($contract, $user, $data);
        } catch (\Exception) {
            return null;
        }
    }

    public function listForFreelance(string $freelanceId): LengthAwarePaginator
    {
        return Review::where("reviewee_id", $freelanceId)
            ->with("reviewer")
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function reply(User $user, string $reviewId, array $data): ?Review
    {
        $review = Review::find($reviewId);
        if (!$review) return null;

        try {
            return $this->replyToReview($review, $user, $data["content"] ?? "");
        } catch (\Exception) {
            return null;
        }
    }

    public function createReview(Contract $contract, User $reviewer, array $data): Review
    {
        if ($contract->client_id !== $reviewer->id && $contract->freelance_id !== $reviewer->id) {
            abort(403, "Vous n'\u00eates pas partie prenante de ce contrat.");
        }

        $hasReviewed = Review::where("contract_id", $contract->id)
            ->where("reviewer_id", $reviewer->id)
            ->exists();

        if ($hasReviewed) {
            abort(409, "Vous avez d\u00e9j\u00e0 laiss\u00e9 un avis pour ce contrat.");
        }

        return DB::transaction(function () use ($contract, $reviewer, $data) {
            $isClient = $contract->client_id === $reviewer->id;
            $targetId = $isClient ? $contract->freelance_id : $contract->client_id;

            $review = Review::create([
                "contract_id" => $contract->id,
                "reviewer_id" => $reviewer->id,
                "reviewee_id" => $targetId,
                "rating" => $data["rating"],
                "comment" => $data["comment"] ?? null,
            ]);

            $this->updateTargetRating($targetId);

            return $review;
        });
    }

    public function replyToReview(Review $review, User $author, string $content): Review
    {
        if ($review->reviewee_id !== $author->id) {
            abort(403, "Vous ne pouvez pas r\u00e9pondre \u00e0 cet avis.");
        }

        ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => $author->id,
            'comment' => $content,
        ]);

        return $review->fresh()->load('reply');
    }

    private function updateTargetRating(string $userId): void
    {
        $avg = Review::where("reviewee_id", $userId)->avg("rating");
        $count = Review::where("reviewee_id", $userId)->count();

        FreelanceProfile::where("user_id", $userId)->update([
            "average_rating" => $avg ? round($avg, 2) : 0,
            "total_reviews" => $count,
        ]);
    }
}
