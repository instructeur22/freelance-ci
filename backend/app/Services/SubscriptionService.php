<?php
namespace App\Services;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionType;
use App\Models\FreelanceProfile;
use App\Models\FreelanceSubscription;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPlanConfig;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function getPlans(): Collection
    {
        return SubscriptionPlanConfig::where("is_active", true)
            ->orderBy("sort_order")
            ->get();
    }

    public function purchase(User $user, array $data): array|FreelanceSubscription|null
    {
        $profile = $user->freelanceProfile;
        if (!$profile) {
            return null;
        }

        $planSlug = $data["plan"] ?? null;
        $billingCycle = $data["billing_cycle"] ?? "monthly";

        if (!in_array($billingCycle, ["monthly", "yearly"])) {
            return null;
        }

        $plan = SubscriptionPlanConfig::where("plan", $planSlug)->first();
        if (!$plan || !$plan->is_active) {
            return null;
        }

        if ($plan->plan === SubscriptionPlan::Starter || $plan->price_monthly <= 0) {
            return $this->activate($profile, $plan->id, $billingCycle, 0);
        }

        $amount = $billingCycle === "yearly" ? $plan->price_yearly : $plan->price_monthly;

        $this->cancelExistingSubscriptions($profile);

        return $this->paymentService->initiatePayment([
            "user_id" => $user->id,
            "type" => TransactionType::Subscription,
            "amount" => (float) $amount,
            "currency" => "XOF",
            "payment_channel" => "mobile_money",
            "payment_operator" => null,
            "description" => "Abonnement {$plan->name} - " . ($user->name ?? $user->email),
            "metadata" => [
                "plan_id" => $plan->id,
                "plan_name" => $plan->name,
                "billing_cycle" => $billingCycle,
                "freelance_profile_id" => $profile->id,
            ],
        ]);
    }

    public function activate(FreelanceProfile $profile, string $planId, string $billingCycle, int $amount): FreelanceSubscription
    {
        $this->cancelExistingSubscriptions($profile);

        $trialDays = $this->getTrialDays();

        $subscription = FreelanceSubscription::create([
            "freelance_profile_id" => $profile->id,
            "plan_id" => $planId,
            "status" => $amount > 0 ? SubscriptionStatus::Active : SubscriptionStatus::Trial,
            "started_at" => now(),
            "ends_at" => $billingCycle === "yearly" ? now()->addYear() : now()->addMonth(),
            "trial_ends_at" => $amount > 0 ? null : now()->addDays($trialDays),
            "billing_cycle" => $billingCycle,
            "amount_paid" => $amount,
            "auto_renew" => true,
        ]);

        return $subscription;
    }

    public function getCurrent(FreelanceProfile $profile): ?FreelanceSubscription
    {
        return FreelanceSubscription::where("freelance_profile_id", $profile->id)
            ->whereIn("status", [SubscriptionStatus::Active, SubscriptionStatus::Trial])
            ->latest("created_at")
            ->first();
    }

    public function cancel(User $user): ?FreelanceSubscription
    {
        $profile = $user->freelanceProfile;
        if (!$profile) {
            return null;
        }

        $subscription = $this->getCurrent($profile);
        if (!$subscription || $subscription->status === SubscriptionStatus::Cancelled) {
            return null;
        }

        $subscription->update([
            "status" => SubscriptionStatus::Cancelled,
            "auto_renew" => false,
            "cancelled_at" => now(),
        ]);

        return $subscription->fresh();
    }

    public function upgrade(User $user, string $newPlanSlug, string $billingCycle): array|FreelanceSubscription|null
    {
        $profile = $user->freelanceProfile;
        if (!$profile) {
            return null;
        }

        $current = $this->getCurrent($profile);
        $newPlan = SubscriptionPlanConfig::where("plan", $newPlanSlug)->first();

        if (!$newPlan || !$newPlan->is_active) {
            return null;
        }

        if ($current && $current->planConfig->plan === $newPlan->plan) {
            return null;
        }

        return $this->purchase($user, [
            "plan" => $newPlanSlug,
            "billing_cycle" => $billingCycle,
        ]);
    }

    private function cancelExistingSubscriptions(FreelanceProfile $profile): void
    {
        FreelanceSubscription::where("freelance_profile_id", $profile->id)
            ->whereIn("status", [SubscriptionStatus::Active, SubscriptionStatus::Trial])
            ->update([
                "status" => SubscriptionStatus::Cancelled,
                "auto_renew" => false,
                "cancelled_at" => now(),
            ]);
    }

    private function getTrialDays(): int
    {
        $setting = PlatformSetting::where("key", "trial_duration_days")->first();
        return $setting ? (int) $setting->value : 14;
    }
}
