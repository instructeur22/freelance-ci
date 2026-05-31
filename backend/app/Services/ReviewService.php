<?php
namespace App\Services;

use App\Models\Contract;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
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
                "target_id" => $targetId,
                "rating" => $data["rating"],
                "comment" => $data["comment"] ?? null,
            ]);

            $this->updateTargetRating($targetId);

            return $review;
        });
    }

    public function replyToReview(Review $review, User $author, string $content): Review
    {
        if ($review->target_id !== $author->id) {
            abort(403, "Vous ne pouvez pas r\u00e9pondre \u00e0 cet avis.");
        }

        $review->update(["reply" => $content, "replied_at" => now()]);
        return $review->fresh();
    }

    private function updateTargetRating(string $userId): void
    {
        $avg = Review::where("target_id", $userId)->avg("rating");
        $count = Review::where("target_id", $userId)->count();

        User::where("id", $userId)->update([
            "rating" => round($avg, 2),
            "reviews_count" => $count,
        ]);
    }
}
