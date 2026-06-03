<?php
namespace App\Services;

use App\Enums\BoostDuration;
use App\Enums\BoostTarget;
use App\Enums\PaymentChannel;
use App\Enums\TransactionType;
use App\Models\Boost;
use App\Models\FreelanceProfile;
use App\Models\PlatformSetting;
use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BoostService
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function purchase(User $user, array $data): ?array
    {
        $profile = $user->freelanceProfile;
        if (!$profile) {
            return null;
        }

        $target = $data["target"] ?? $data["target_type"] ?? null;
        $duration = $data["duration"] ?? null;
        $targetId = $data["target_id"] ?? null;

        if (!in_array($target, ["profile", "project"])) {
            return null;
        }
        if (!in_array($duration, ["7_days", "30_days"])) {
            return null;
        }

        if ($target === "project" && $targetId) {
            $project = Project::find($targetId);
            if (!$project || $project->client_id !== $user->id) {
                return null;
            }
        }

        $this->deactivateExistingForTarget($profile, $target, $targetId);

        $activeCount = $profile->boosts()->where("is_active", true)->count();
        $maxBoosts = $this->getMaxBoostsPerProfile();
        if ($activeCount >= $maxBoosts) {
            return null;
        }

        $amount = $this->getBoostPrice($target, $duration);
        if (!$amount) {
            return null;
        }

        return $this->paymentService->initiatePayment([
            "user_id" => $user->id,
            "type" => $target === "profile" ? TransactionType::BoostProfile : TransactionType::BoostProject,
            "amount" => $amount,
            "currency" => "XOF",
            "payment_channel" => PaymentChannel::MOBILE_MONEY->value,
            "payment_operator" => null,
            "description" => "Boost " . ($target === "profile" ? "profil" : "projet") . " - " . ($user->name ?? $user->email),
            "metadata" => [
                "target" => $target,
                "target_id" => $targetId,
                "duration" => $duration,
                "freelance_profile_id" => $profile->id,
            ],
        ]);
    }

    public function activate(FreelanceProfile $profile, string $target, ?string $targetId, string $duration, int $amount): Boost
    {
        $this->deactivateExistingForTarget($profile, $target, $targetId);

        $days = $duration === "30_days" ? 30 : 7;

        $boost = Boost::create([
            "freelance_profile_id" => $profile->id,
            "target" => $target,
            "target_id" => $target === "profile" ? null : $targetId,
            "duration" => $duration,
            "amount_paid" => $amount,
            "is_active" => true,
            "started_at" => now(),
            "ends_at" => now()->addDays($days),
        ]);

        return $boost;
    }

    public function listForUser(User $user): LengthAwarePaginator
    {
        $profile = $user->freelanceProfile;
        if (!$profile) {
            return Boost::whereRaw("1 = 0")->paginate(20);
        }

        return Boost::where("freelance_profile_id", $profile->id)
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function getActiveBoosts(FreelanceProfile $profile): array
    {
        return Boost::where("freelance_profile_id", $profile->id)
            ->where("is_active", true)
            ->where("ends_at", ">", now())
            ->get()
            ->all();
    }

    public function getBoostPrice(string $target, string $duration): ?float
    {
        $key = "boost_{$target}_price_{$duration}";
        $setting = PlatformSetting::where("key", $key)->first();
        return $setting ? (float) $setting->value : null;
    }

    public function revoke(string $boostId): bool
    {
        $boost = Boost::find($boostId);
        if (!$boost) {
            return false;
        }

        return $boost->update(["is_active" => false]);
    }

    private function getMaxBoostsPerProfile(): int
    {
        $setting = PlatformSetting::where("key", "max_boost_per_profile")->first();
        return $setting ? (int) $setting->value : 3;
    }

    private function deactivateExistingForTarget(FreelanceProfile $profile, string $target, ?string $targetId): void
    {
        $query = Boost::where("freelance_profile_id", $profile->id)->where("is_active", true);

        if ($target === "profile") {
            $query->where("target", BoostTarget::Profile);
        } elseif ($target === "project" && $targetId) {
            $query->where("target", BoostTarget::Project)->where("target_id", $targetId);
        }

        $query->update(["is_active" => false]);
    }
}
