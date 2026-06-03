<?php
namespace App\Services;

use App\Models\ClientProfile;
use App\Models\FreelanceProfile;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProfileService
{
    public function getFullProfile(User $user): User
    {
        return $user->load([
            "profile",
            "clientProfile",
            "freelanceProfile.skills",
            "portfolioItems",
            "wallet",
        ]);
    }

    public function updateCommonProfile(User $user, array $data): Profile
    {
        $profile = $user->profile()->firstOrCreate([]);
        $profile->update($data);
        return $profile->fresh();
    }

    public function getClientProfile(User $user): ?ClientProfile
    {
        return $user->clientProfile;
    }

    public function updateClientProfile(User $user, array $data): ClientProfile
    {
        $profile = $user->clientProfile()->firstOrCreate([]);
        $profile->update($data);
        return $profile->fresh();
    }

    public function getFreelanceProfile(User $user): ?FreelanceProfile
    {
        return $user->freelanceProfile;
    }

    public function updateFreelanceProfile(User $user, array $data): FreelanceProfile
    {
        $profile = $user->freelanceProfile()->firstOrCreate([]);
        $profile->update($data);
        return $profile->fresh();
    }

    public function addSkill(User $user, array $data): void
    {
        $profile = $user->freelanceProfile;
        if (!$profile) return;

        $skillId = $data["skill_id"] ?? $data["id"] ?? null;
        $level = $data["proficiency_level"] ?? $data["level"] ?? null;
        if ($skillId && !$profile->skills()->where('skill_id', $skillId)->exists()) {
            $profile->skills()->attach($skillId, [
                'id' => (string) \Illuminate\Support\Str::orderedUuid(),
                'proficiency_level' => $level,
            ]);
        }
    }

    public function removeSkill(User $user, mixed $skillId): void
    {
        $profile = $user->freelanceProfile;
        if (!$profile) return;

        $profile->skills()->detach($skillId);
    }

    public function addPortfolioItem(User $user, array $data): PortfolioItem
    {
        $profile = $user->freelanceProfile;
        if (!$profile) {
            throw new \RuntimeException("Aucun profil freelance trouvé");
        }
        return $profile->portfolioItems()->create($data);
    }

    public function removePortfolioItem(User $user, string $itemId): void
    {
        $user->portfolioItems()->where("id", $itemId)->delete();
    }

    public function listFreelances(array $filters): LengthAwarePaginator
    {
        $query = User::where("role", \App\Enums\UserRole::Freelance->value)
            ->with(["profile", "freelanceProfile.skills", "portfolioItems"]);

        if (!empty($filters["search"])) {
            $search = $filters["search"];
            $query->where(function (Builder $q) use ($search) {
                $q->where("first_name", "like", "%{$search}%")
                  ->orWhere("last_name", "like", "%{$search}%")
                  ->orWhereHas("profile", function (Builder $p) use ($search) {
                      $p->where("bio", "like", "%{$search}%")
                        ->orWhere("professional_title", "like", "%{$search}%");
                  })
                  ->orWhereHas("freelanceProfile.skills", function (Builder $s) use ($search) {
                      $s->where("name", "like", "%{$search}%");
                  });
            });
        }

        if (!empty($filters["category"])) {
            $query->whereHas("freelanceProfile", function (Builder $q) use ($filters) {
                $q->where("professional_title", "like", "%{$filters["category"]}%");
            });
        }

        if (!empty($filters["min_rate"])) {
            $query->whereHas("freelanceProfile", function (Builder $q) use ($filters) {
                $q->where("hourly_rate_min", ">=", $filters["min_rate"]);
            });
        }

        if (!empty($filters["max_rate"])) {
            $query->whereHas("freelanceProfile", function (Builder $q) use ($filters) {
                $q->where("hourly_rate_max", "<=", $filters["max_rate"]);
            });
        }

        if (!empty($filters["skill_ids"])) {
            $skillIds = is_array($filters["skill_ids"]) ? $filters["skill_ids"] : explode(",", $filters["skill_ids"]);
            $query->whereHas("freelanceProfile.skills", function (Builder $q) use ($skillIds) {
                $q->whereIn("skill_id", $skillIds);
            }, ">=", count($skillIds));
        }

        if (!empty($filters["rating_min"])) {
            $query->where("average_rating", ">=", $filters["rating_min"]);
        }

        $sortField = $filters["sort_by"] ?? "created_at";
        $sortOrder = $filters["sort_order"] ?? "desc";
        $query->orderBy($sortField, $sortOrder);

        $perPage = $filters["per_page"] ?? 12;
        return $query->paginate($perPage);
    }

    public function getFreelanceDetail(string $id): ?User
    {
        return User::where("role", \App\Enums\UserRole::Freelance->value)
            ->with(["profile", "freelanceProfile.skills", "portfolioItems"])
            ->find($id);
    }
}
