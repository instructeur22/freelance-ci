<?php
namespace App\Services;

use App\Enums\PaymentChannel;
use App\Enums\TransactionType;
use App\Models\FreelanceProfile;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\VerifiedBadge;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function purchase(User $user): ?array
    {
        $profile = $user->freelanceProfile;
        if (!$profile) {
            return null;
        }

        $price = $this->getBadgePrice();
        if (!$price) {
            return null;
        }

        $data = [
            "user_id" => $user->id,
            "type" => TransactionType::BadgeVerified,
            "amount" => $price,
            "currency" => "XOF",
            "payment_channel" => PaymentChannel::MOBILE_MONEY->value,
            "payment_operator" => null,
            "description" => "Achat badge vérifié - " . ($user->name ?? $user->email),
            "metadata" => [
                "badge_type" => "verified",
                "freelance_profile_id" => $profile->id,
            ],
        ];

        return $this->paymentService->initiatePayment($data);
    }

    public function activate(FreelanceProfile $profile, ?string $verificationId, int $amount): VerifiedBadge
    {
        $this->deactivateExisting($profile);

        $badge = VerifiedBadge::create([
            "freelance_profile_id" => $profile->id,
            "verification_id" => $verificationId,
            "badge_type" => "verified",
            "is_active" => true,
            "granted_at" => now(),
            "expires_at" => now()->addYear(),
        ]);

        return $badge;
    }

    public function revoke(string $badgeId): bool
    {
        $badge = VerifiedBadge::find($badgeId);
        if (!$badge) {
            return false;
        }

        return $badge->update(["is_active" => false]);
    }

    public function getActiveBadge(FreelanceProfile $profile): ?VerifiedBadge
    {
        return $profile->activeBadge()->first();
    }

    public function getBadgePrice(): ?float
    {
        $setting = PlatformSetting::where("key", "badge_verified_price")->first();
        return $setting ? (float) $setting->value : null;
    }

    private function deactivateExisting(FreelanceProfile $profile): void
    {
        VerifiedBadge::where("freelance_profile_id", $profile->id)
            ->where("is_active", true)
            ->update(["is_active" => false]);
    }
}
