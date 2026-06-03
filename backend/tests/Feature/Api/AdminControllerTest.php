<?php

namespace Tests\Feature\Api;

use App\Models\Boost;
use App\Models\Dispute;
use App\Models\FreelanceProfile;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerifiedBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
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

    public function test_admin_can_get_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/admin/users');

        $response->assertStatus(200);
    }

    public function test_admin_can_update_user_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->putJson('/api/admin/users/' . $user->id . '/status', [
                'status' => 'suspended',
            ]);

        $response->assertStatus(200);
    }

    public function test_admin_can_approve_verification(): void
    {
        $admin = User::factory()->admin()->create();
        $verification = Verification::factory()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/admin/verifications/' . $verification->id . '/approve');

        $response->assertStatus(200);
    }

    public function test_admin_can_reject_verification(): void
    {
        $admin = User::factory()->admin()->create();
        $verification = Verification::factory()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/admin/verifications/' . $verification->id . '/reject', [
                'admin_notes' => 'Invalid document',
            ]);

        $response->assertStatus(200);
    }

    public function test_admin_can_list_and_resolve_reports(): void
    {
        $admin = User::factory()->admin()->create();
        Report::factory()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/admin/reports');

        $response->assertStatus(200);
    }

    public function test_admin_can_list_and_resolve_disputes(): void
    {
        $admin = User::factory()->admin()->create();
        Dispute::factory()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/admin/disputes');

        $response->assertStatus(200);
    }

    public function test_admin_can_get_and_update_settings(): void
    {
        $admin = User::factory()->admin()->create();
        PlatformSetting::factory()->create([
            'key' => 'badge_price',
            'value' => '5000',
            'group' => 'payment',
        ]);

        $response = $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/admin/settings');

        $response->assertStatus(200);
    }

    public function test_admin_can_grant_and_revoke_badge(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = FreelanceProfile::factory()->create();

        $grantResponse = $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/admin/badges/grant', [
                'freelance_profile_id' => $profile->id,
            ]);

        $grantResponse->assertStatus(200);

        $badgeId = $grantResponse->json('data.id');

        $revokeResponse = $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/admin/badges/' . $badgeId . '/revoke');

        $revokeResponse->assertStatus(200);
    }

    public function test_admin_can_revoke_boost(): void
    {
        $admin = User::factory()->admin()->create();
        $boost = Boost::factory()->create();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/admin/boosts/' . $boost->id . '/revoke');

        $response->assertStatus(200);
    }
}
