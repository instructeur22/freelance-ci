<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = new WalletService();
    }

    public function test_getWallet_creates_wallet_if_not_exists(): void
    {
        $user = User::factory()->create();

        $wallet = $this->walletService->getWallet($user);

        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals(0, $wallet->available_xof);
    }

    public function test_getWallet_returns_existing_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->withBalance(50000)->create(['user_id' => $user->id]);

        $result = $this->walletService->getWallet($user);

        $this->assertEquals($wallet->id, $result->id);
        $this->assertEquals(50000, $result->available_xof);
    }

    public function test_getTransactions_returns_paginated_transactions(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $transactions = $this->walletService->getTransactions($user);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $transactions);
    }

    public function test_requestWithdrawal_creates_withdrawal_request(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->withBalance(100000)->create(['user_id' => $user->id]);

        $withdrawal = $this->walletService->requestWithdrawal($user, [
            'amount' => 50000,
            'method' => 'orange_money',
            'account_identifier' => '+2250101020304',
        ]);

        $this->assertNotFalse($withdrawal);
        $this->assertInstanceOf(WithdrawalRequest::class, $withdrawal);
    }

    public function test_requestWithdrawal_fails_if_insufficient_balance(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->withBalance(1000)->create(['user_id' => $user->id]);

        $withdrawal = $this->walletService->requestWithdrawal($user, [
            'amount' => 50000,
            'method' => 'orange_money',
            'account_identifier' => '+2250101020304',
        ]);

        $this->assertFalse($withdrawal);
    }
}
