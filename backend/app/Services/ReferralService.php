<?php
namespace App\Services;

use App\Enums\ReferralStatus;
use App\Models\PlatformSetting;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Support\Str;

class ReferralService
{
    public function getOrCreateCode(User $user): ReferralCode
    {
        $code = ReferralCode::where("user_id", $user->id)->first();

        if (!$code) {
            $code = ReferralCode::create([
                "user_id" => $user->id,
                "code" => $this->generateUniqueCode($user),
            ]);
        }

        return $code;
    }

    public function trackReferral(User $referredUser, string $referralCode): ?Referral
    {
        $code = ReferralCode::where("code", $referralCode)->first();
        if (!$code || $code->user_id === $referredUser->id) {
            return null;
        }

        $existing = Referral::where("referred_id", $referredUser->id)->first();
        if ($existing) {
            return null;
        }

        return Referral::create([
            "referrer_id" => $code->user_id,
            "referred_id" => $referredUser->id,
            "referral_code_id" => $code->id,
            "status" => ReferralStatus::Pending,
            "reward_amount" => 0,
        ]);
    }

    public function completeReferral(User $referredUser): ?Referral
    {
        $referral = Referral::where("referred_id", $referredUser->id)
            ->where("status", ReferralStatus::Pending)
            ->first();

        if (!$referral) {
            return null;
        }

        $rewardAmount = $this->getRewardAmount();

        $referral->update([
            "status" => ReferralStatus::Completed,
            "reward_amount" => $rewardAmount,
        ]);

        $referrer = $referral->referrer;
        if ($referrer && $rewardAmount > 0) {
            $referrer->wallet()->increment("available_xof", $rewardAmount);
            $referrer->wallet()->increment("total_earned_xof", $rewardAmount);

            $wallet = $referrer->wallet;
            $wallet->transactions()->create([
                "direction" => "credit",
                "amount_xof" => $rewardAmount,
                "balance_before_xof" => $wallet->available_xof - $rewardAmount,
                "balance_after_xof" => $wallet->available_xof,
                "description" => "Récompense parrainage",
            ]);
        }

        return $referral->fresh();
    }

    public function getReferralStats(User $user): array
    {
        $code = ReferralCode::where("user_id", $user->id)->first();

        return [
            "code" => $code?->code,
            "total_referrals" => Referral::where("referrer_id", $user->id)->count(),
            "completed_referrals" => Referral::where("referrer_id", $user->id)
                ->whereIn("status", [ReferralStatus::Completed, ReferralStatus::Paid])
                ->count(),
            "total_earned" => (float) Referral::where("referrer_id", $user->id)
                ->whereIn("status", [ReferralStatus::Completed, ReferralStatus::Paid])
                ->sum("reward_amount"),
            "reward_per_referral" => $this->getRewardAmount(),
        ];
    }

    public function getReferrals(User $user): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Referral::where("referrer_id", $user->id)
            ->with("referred:id,first_name,last_name,email")
            ->latest()
            ->paginate(20);
    }

    public function getRewardAmount(): float
    {
        $setting = PlatformSetting::where("key", "referral_reward_amount")->first();
        return $setting ? (float) $setting->value : 5000;
    }

    private function generateUniqueCode(User $user): string
    {
        $prefix = strtoupper(Str::substr($user->first_name ?? "USER", 0, 3));
        $suffix = strtoupper(Str::random(4));

        $code = $prefix . $suffix;

        while (ReferralCode::where("code", $code)->exists()) {
            $suffix = strtoupper(Str::random(4));
            $code = $prefix . $suffix;
        }

        return $code;
    }
}
