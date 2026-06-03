<?php

namespace Tests\Feature\Api;

use App\Enums\BoostTarget;
use App\Models\Boost;
use App\Models\FreelanceProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BoostControllerTest extends TestCase
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

    public function test_can_purchase_boost(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-boost',
                'reference' => 'ref-boost',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-boost',
                'status' => 'PENDING',
            ], 201),
        ]);

        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/boosts/purchase', [
                'target_type' => BoostTarget::Profile->value,
                'duration' => '7_days',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['payment_url', 'transaction_id']]);
    }

    public function test_can_list_boosts(): void
    {
        $user = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $user->id]);
        Boost::factory()->count(2)->create([
            'freelance_profile_id' => $profile->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/boosts');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
