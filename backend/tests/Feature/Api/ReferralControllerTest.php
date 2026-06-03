<?php

namespace Tests\Feature\Api;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_referral_code(): void
    {
        $user = User::factory()->create();
        ReferralCode::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/referrals/code');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['code']]);
    }

    public function test_can_get_referral_stats(): void
    {
        $user = User::factory()->create();
        ReferralCode::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/referrals/stats');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['total_referrals', 'completed_referrals', 'total_earned']]);
    }

    public function test_can_list_referrals(): void
    {
        $user = User::factory()->create();
        ReferralCode::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/referrals');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
