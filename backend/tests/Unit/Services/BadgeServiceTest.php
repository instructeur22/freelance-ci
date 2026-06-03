<?php

namespace Tests\Unit\Services;

use App\Models\FreelanceProfile;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\VerifiedBadge;
use App\Models\Wallet;
use App\Services\BadgeService;
use App\Services\GeniusPayService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BadgeService $badgeService;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'geniuspay.api_key' => 'pk_test_key',
            'geniuspay.api_secret' => 'sk_test_secret',
            'geniuspay.webhook_secret' => 'whsec_test',
            'geniuspay.mode' => 'test',
        ]);

        $geniusPayService = new GeniusPayService();
        $paymentService = new PaymentService($geniusPayService);
        $this->badgeService = new BadgeService($paymentService);
    }

    public function test_getBadgePrice_returns_price_from_settings(): void
    {
        $price = $this->badgeService->getBadgePrice();

        $this->assertEquals(25000, $price);
    }

    public function test_activate_creates_active_badge(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);

        $badge = $this->badgeService->activate($profile, null, 5000);

        $this->assertInstanceOf(VerifiedBadge::class, $badge);
        $this->assertTrue($badge->is_active);
        $this->assertEquals($profile->id, $badge->freelance_profile_id);
    }

    public function test_purchase_initiates_payment(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-badge',
                'reference' => 'ref-badge',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-badge',
                'status' => 'PENDING',
            ], 201),
        ]);

        $freelance = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $freelance->id]);

        $result = $this->badgeService->purchase($freelance);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment_url', $result);
    }

    public function test_revoke_deactivates_badge(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        $badge = VerifiedBadge::factory()->create([
            'freelance_profile_id' => $profile->id,
            'is_active' => true,
        ]);

        $result = $this->badgeService->revoke($badge->id);

        $this->assertTrue($result);
        $this->assertFalse($badge->fresh()->is_active);
    }

    public function test_getActiveBadge_returns_active_badge(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        VerifiedBadge::factory()->create([
            'freelance_profile_id' => $profile->id,
            'is_active' => true,
            'expires_at' => now()->addMonths(11),
        ]);

        $badge = $this->badgeService->getActiveBadge($profile);

        $this->assertNotNull($badge);
        $this->assertTrue($badge->is_active);
    }

    public function test_getActiveBadge_returns_null_when_expired(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        VerifiedBadge::factory()->expired()->create([
            'freelance_profile_id' => $profile->id,
        ]);

        $badge = $this->badgeService->getActiveBadge($profile);

        $this->assertNull($badge);
    }
}
