<?php

namespace Tests\Unit\Services;

use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\FreelanceProfile;
use App\Models\FreelanceSubscription;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPlanConfig;
use App\Models\User;
use App\Models\Wallet;
use App\Services\GeniusPayService;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionService $subscriptionService;

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
        $this->subscriptionService = new SubscriptionService($paymentService);
    }

    public function test_getPlans_returns_active_plans(): void
    {
        $starter = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Starter)->first();
        $starter->update(['is_active' => false]);

        $plans = $this->subscriptionService->getPlans();

        $this->assertCount(2, $plans);
    }

    public function test_activate_creates_active_subscription(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        $plan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Pro)->first();

        $subscription = $this->subscriptionService->activate($profile, $plan->id, 'monthly', 5000);

        $this->assertInstanceOf(FreelanceSubscription::class, $subscription);
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status);
        $this->assertEquals($profile->id, $subscription->freelance_profile_id);
    }

    public function test_getCurrent_returns_active_subscription(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        $plan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Pro)->first();

        FreelanceSubscription::factory()->create([
            'freelance_profile_id' => $profile->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $current = $this->subscriptionService->getCurrent($profile);

        $this->assertNotNull($current);
        $this->assertEquals(SubscriptionStatus::Active, $current->status);
    }

    public function test_cancel_cancels_active_subscription(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        $plan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Pro)->first();

        FreelanceSubscription::factory()->create([
            'freelance_profile_id' => $profile->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $result = $this->subscriptionService->cancel($freelance);

        $this->assertNotNull($result);
        $this->assertEquals(SubscriptionStatus::Cancelled, $result->status);
    }

    public function test_upgrade_creates_new_subscription_and_cancels_old(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        $starterPlan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Starter)->first();
        $proPlan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Pro)->first();

        $oldSubscription = FreelanceSubscription::factory()->create([
            'freelance_profile_id' => $profile->id,
            'plan_id' => $starterPlan->id,
            'status' => SubscriptionStatus::Active,
        ]);

        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-upgrade',
                'reference' => 'ref-upgrade',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-upgrade',
                'status' => 'PENDING',
            ], 201),
        ]);

        $result = $this->subscriptionService->upgrade($freelance, 'pro', 'monthly');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment_url', $result);
        $this->assertEquals(SubscriptionStatus::Cancelled, $oldSubscription->fresh()->status);
    }
}
