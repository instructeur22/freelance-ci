<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_can_instantiate(): void
    {
        $this->assertInstanceOf(AuthService::class, $this->authService);
    }

    public function test_register_creates_user(): void
    {
        $user = $this->authService->register([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
            'password' => 'secret123',
            'role' => 'client',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('jean@example.com', $user->email);
        $this->assertEquals(UserRole::Client, $user->role);
        $this->assertEquals(AccountStatus::Active, $user->status);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_register_creates_referral_code(): void
    {
        $user = $this->authService->register([
            'first_name' => 'Marie',
            'last_name' => 'Curie',
            'email' => 'marie@example.com',
            'password' => 'secret123',
            'role' => 'freelance',
        ]);

        $this->assertDatabaseHas('referral_codes', [
            'user_id' => $user->id,
        ]);
    }

    public function test_register_with_referral_code_tracks_referral(): void
    {
        $referrer = User::factory()->create();
        $referralCode = ReferralCode::factory()->create(['user_id' => $referrer->id]);

        $user = $this->authService->register([
            'first_name' => 'Paul',
            'last_name' => 'Martin',
            'email' => 'paul@example.com',
            'password' => 'secret123',
            'role' => 'freelance',
            'referral_code' => $referralCode->code,
        ]);

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
        ]);
    }

    public function test_findOrCreateFromSupabase_creates_new_user(): void
    {
        $user = $this->authService->findOrCreateFromSupabase('sb-user-123', [
            'email' => 'supabase@example.com',
            'first_name' => 'Test',
            'role' => 'freelance',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('supabase@example.com', $user->email);
    }

    public function test_findOrCreateFromSupabase_returns_existing_user(): void
    {
        $existing = User::factory()->create(['email' => 'existing@example.com']);

        $user = $this->authService->findOrCreateFromSupabase('sb-user-456', [
            'email' => 'existing@example.com',
            'first_name' => 'Existing',
            'role' => 'freelance',
        ]);

        $this->assertEquals($existing->id, $user->id);
    }
}
