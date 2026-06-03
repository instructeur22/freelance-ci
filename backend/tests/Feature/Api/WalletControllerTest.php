<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_show_wallet(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->withBalance(50000)->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/wallet');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['available_xof', 'pending_xof']]);
    }

    public function test_can_list_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        WalletTransaction::factory()->count(2)->create(['wallet_id' => $wallet->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/wallet/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_request_withdrawal(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->withBalance(100000)->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/wallet/withdraw', [
                'amount' => 50000,
                'method' => 'wave',
                'account_identifier' => '+2250101020304',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_withdrawal_fails_if_insufficient_balance(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->withBalance(1000)->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/wallet/withdraw', [
                'amount' => 50000,
                'method' => 'wave',
                'account_identifier' => '+2250101020304',
            ]);

        $response->assertStatus(400);
    }
}
