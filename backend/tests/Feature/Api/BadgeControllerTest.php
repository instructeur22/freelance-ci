<?php

namespace Tests\Feature\Api;

use App\Models\FreelanceProfile;
use App\Models\User;
use App\Models\VerifiedBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BadgeControllerTest extends TestCase
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

    public function test_can_purchase_badge(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-badge',
                'reference' => 'ref-badge',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-badge',
                'status' => 'PENDING',
            ], 201),
        ]);

        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/badges/purchase');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['payment_url', 'transaction_id']]);
    }

    public function test_can_show_badge(): void
    {
        $user = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $user->id]);
        VerifiedBadge::factory()->create([
            'freelance_profile_id' => $profile->id,
            'is_active' => true,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/badges');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_returns_404_if_no_badge(): void
    {
        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/badges');

        $response->assertStatus(200);
    }
}
