<?php
namespace App\Services;

use App\Models\PortfolioItem;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProfileService
{
    public function getProfile(User $user): User
    {
        return $user->load([
            "profile",
            "clientProfile",
            "freelanceProfile",
            "skills",
            "portfolioItems",
            "wallet",
            "subscription",
        ]);
    }

    public function updateCommonProfile(User $user, array $data): void
    {
        $profile = $user->profile()->firstOrCreate([]);
        $profile->update($data);
    }

    public function updateClientProfile(User $user, array $data): void
    {
        $profile = $user->clientProfile()->firstOrCreate([]);
        $profile->update($data);
    }

    public function updateFreelanceProfile(User $user, array $data): void
    {
        $profile = $user->freelanceProfile()->firstOrCreate([]);
        $profile->update($data);
    }

    public function addSkill(User $user, mixed $skillId, ?string $level = null): void
    {
        $user->skills()->syncWithoutDetaching([
            $skillId => ["level" => $level],
        ]);
    }

    public function removeSkill(User $user, mixed $skillId): void
    {
        $user->skills()->detach($skillId);
    }

    public function addPortfolioItem(User $user, array $data): PortfolioItem
    {
        return $user->portfolioItems()->create($data);
    }

    public function removePortfolioItem(User $user, string $itemId): void
    {
        $user->portfolioItems()->where("id", $itemId)->delete();
    }

    public function getFreelanceListing(array $filters): LengthAwarePaginator
    {
        $query = User::where("role", \App\Enums\UserRole::Freelance->value)
            ->with(["profile", "freelanceProfile", "skills", "portfolioItems"]);

        if (!empty($filters["search"])) {
            $search = $filters["search"];
            $query->where(function (Builder $q) use ($search) {
                $q->where("first_name", "like", "%{$search}%")
                  ->orWhere("last_name", "like", "%{$search}%")
                  ->orWhereHas("profile", function (Builder $p) use ($search) {
                      $p->where("bio", "like", "%{$search}%")
                        ->orWhere("title", "like", "%{$search}%");
                  })
                  ->orWhereHas("skills", function (Builder $s) use ($search) {
                      $s->where("name", "like", "%{$search}%");
                  });
            });
        }

        if (!empty($filters["category"])) {
            $query->whereHas("freelanceProfile", function (Builder $q) use ($filters) {
                $q->where("category", $filters["category"]);
            });
        }

        if (!empty($filters["min_rate"])) {
            $query->whereHas("freelanceProfile", function (Builder $q) use ($filters) {
                $q->where("hourly_rate", ">=", $filters["min_rate"]);
            });
        }

        if (!empty($filters["max_rate"])) {
            $query->whereHas("freelanceProfile", function (Builder $q) use ($filters) {
                $q->where("hourly_rate", "<=", $filters["max_rate"]);
            });
        }

        if (!empty($filters["skill_ids"])) {
            $skillIds = is_array($filters["skill_ids"]) ? $filters["skill_ids"] : explode(",", $filters["skill_ids"]);
            $query->whereHas("skills", function (Builder $q) use ($skillIds) {
                $q->whereIn("skill_id", $skillIds);
            }, ">=", count($skillIds));
        }

        if (!empty($filters["rating_min"])) {
            $query->where("rating", ">=", $filters["rating_min"]);
        }

        $sortField = $filters["sort_by"] ?? "created_at";
        $sortOrder = $filters["sort_order"] ?? "desc";
        $query->orderBy($sortField, $sortOrder);

        $perPage = $filters["per_page"] ?? 12;
        return $query->paginate($perPage);
    }
}
