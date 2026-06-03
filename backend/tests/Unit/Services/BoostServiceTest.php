<?php

namespace Tests\Unit\Services;

use App\Enums\BoostDuration;
use App\Enums\BoostTarget;
use App\Models\Boost;
use App\Models\FreelanceProfile;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BoostService;
use App\Services\GeniusPayService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BoostServiceTest extends TestCase
{
    use RefreshDatabase;

    private BoostService $boostService;

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
        $this->boostService = new BoostService($paymentService);
    }

    public function test_getBoostPrice_returns_correct_price(): void
    {
        $price = $this->boostService->getBoostPrice(BoostTarget::Profile->value, BoostDuration::SevenDays->value);

        $this->assertEquals(5000, $price);
    }

    public function test_activate_creates_active_boost(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);

        $boost = $this->boostService->activate($profile, BoostTarget::Profile->value, null, BoostDuration::SevenDays->value, 3000);

        $this->assertInstanceOf(Boost::class, $boost);
        $this->assertTrue($boost->is_active);
        $this->assertEquals(BoostTarget::Profile, $boost->target);
        $this->assertEquals(BoostDuration::SevenDays, $boost->duration);
    }

    public function test_activate_deactivates_previous_same_type_boosts(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);

        $oldBoost = Boost::factory()->create([
            'freelance_profile_id' => $profile->id,
            'target' => BoostTarget::Profile,
            'is_active' => true,
        ]);

        $this->boostService->activate($profile, BoostTarget::Profile->value, null, BoostDuration::SevenDays->value, 3000);

        $this->assertFalse($oldBoost->fresh()->is_active);
    }

    public function test_revoke_deactivates_boost(): void
    {
        $boost = Boost::factory()->create(['is_active' => true]);

        $result = $this->boostService->revoke($boost->id);

        $this->assertTrue($result);
        $this->assertFalse($boost->fresh()->is_active);
    }
}
