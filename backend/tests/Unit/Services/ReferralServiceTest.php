<?php

namespace Tests\Unit\Services;

use App\Enums\ReferralStatus;
use App\Models\PlatformSetting;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferralService $referralService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->referralService = new ReferralService();
    }

    public function test_getOrCreateCode_creates_code(): void
    {
        $user = User::factory()->create();

        $code = $this->referralService->getOrCreateCode($user);

        $this->assertInstanceOf(ReferralCode::class, $code);
        $this->assertEquals($user->id, $code->user_id);
    }

    public function test_getOrCreateCode_returns_existing_code(): void
    {
        $user = User::factory()->create();
        $existingCode = ReferralCode::factory()->create(['user_id' => $user->id]);

        $code = $this->referralService->getOrCreateCode($user);

        $this->assertEquals($existingCode->id, $code->id);
    }

    public function test_trackReferral_creates_referral(): void
    {
        $referrer = User::factory()->create();
        $referredUser = User::factory()->create();
        $referralCode = ReferralCode::factory()->create(['user_id' => $referrer->id]);

        $referral = $this->referralService->trackReferral($referredUser, $referralCode->code);

        $this->assertNotNull($referral);
        $this->assertInstanceOf(Referral::class, $referral);
        $this->assertEquals($referrer->id, $referral->referrer_id);
        $this->assertEquals($referredUser->id, $referral->referred_id);
        $this->assertEquals(ReferralStatus::Pending, $referral->status);
    }

    public function test_trackReferral_prevents_self_referral(): void
    {
        $user = User::factory()->create();
        $referralCode = ReferralCode::factory()->create(['user_id' => $user->id]);

        $referral = $this->referralService->trackReferral($user, $referralCode->code);

        $this->assertNull($referral);
    }

    public function test_getReferralStats_returns_correct_counts(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create();

        Referral::factory()->count(3)->create([
            'referrer_id' => $referrer->id,
            'status' => ReferralStatus::Pending,
        ]);
        Referral::factory()->count(2)->create([
            'referrer_id' => $referrer->id,
            'status' => ReferralStatus::Paid,
        ]);

        $stats = $this->referralService->getReferralStats($referrer);

        $this->assertEquals(5, $stats['total_referrals']);
        $this->assertEquals(2, $stats['completed_referrals']);
    }

    public function test_getRewardAmount_returns_setting_value(): void
    {
        $amount = $this->referralService->getRewardAmount();

        $this->assertEquals(5000, $amount);
    }
}
