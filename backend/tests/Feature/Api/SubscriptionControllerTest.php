<?php

namespace Tests\Feature\Api;

use App\Enums\SubscriptionPlan;
use App\Models\FreelanceProfile;
use App\Models\FreelanceSubscription;
use App\Models\SubscriptionPlanConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'geniuspay.api_key' => 'pk_test_key',
            'geniuspay.api_secret' => 'sk_test_secret',
            'geniuspay.webhook_secret' => 'whsec_test',
            'geniuspay.mode' => 'test',
        ]);
    }

    public function test_can_list_plans(): void
    {
        $response = $this->getJson('/api/subscriptions/plans');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_purchase_subscription(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-sub',
                'reference' => 'ref-sub',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-sub',
                'status' => 'PENDING',
            ], 201),
        ]);

        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);
        $plan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Pro)->first();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/subscriptions/purchase', [
                'plan' => SubscriptionPlan::Pro->value,
                'billing_cycle' => 'monthly',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['payment_url', 'transaction_id']]);
    }

    public function test_can_show_subscription(): void
    {
        $user = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $user->id]);
        $plan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Pro)->first();
        FreelanceSubscription::factory()->create([
            'freelance_profile_id' => $profile->id,
            'plan_id' => $plan->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/subscriptions');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_cancel_subscription(): void
    {
        $user = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $user->id]);
        $plan = SubscriptionPlanConfig::where('plan', SubscriptionPlan::Pro)->first();
        FreelanceSubscription::factory()->create([
            'freelance_profile_id' => $profile->id,
            'plan_id' => $plan->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/subscriptions/cancel');

        $response->assertStatus(200);
    }
}
