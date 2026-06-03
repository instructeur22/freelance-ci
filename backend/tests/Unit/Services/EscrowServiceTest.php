<?php

namespace Tests\Unit\Services;

use App\Enums\EscrowStatus;
use App\Models\Contract;
use App\Models\Escrow;
use App\Models\Payment;
use App\Models\Wallet;
use App\Services\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowServiceTest extends TestCase
{
    use RefreshDatabase;

    private EscrowService $escrowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->escrowService = new EscrowService();
    }

    public function test_holdFunds_creates_escrow(): void
    {
        $contract = Contract::factory()->active()->create();
        Wallet::factory()->withBalance(999999)->create(['user_id' => $contract->client_id]);

        $escrow = $this->escrowService->holdFunds($contract);

        $this->assertInstanceOf(Escrow::class, $escrow);
        $this->assertEquals($contract->id, $escrow->contract_id);
        $this->assertEquals(EscrowStatus::Holding, $escrow->status);
        $this->assertEquals($contract->total_amount, $escrow->amount);
    }

    public function test_releaseFunds_releases_escrow(): void
    {
        $contract = Contract::factory()->active()->create();
        Wallet::factory()->create(['user_id' => $contract->freelance_id]);
        $escrow = Escrow::factory()->create([
            'contract_id' => $contract->id,
            'status' => EscrowStatus::Holding,
            'amount' => $contract->total_amount,
            'held_amount' => $contract->total_amount,
        ]);

        $result = $this->escrowService->releaseFunds($contract);

        $this->assertInstanceOf(Escrow::class, $result);
        $this->assertEquals(EscrowStatus::Released, $result->status);
        $this->assertEquals($contract->total_amount, $result->released_amount);
    }

    public function test_refundFunds_refunds_escrow(): void
    {
        $contract = Contract::factory()->active()->create();
        Wallet::factory()->create(['user_id' => $contract->client_id]);
        $escrow = Escrow::factory()->create([
            'contract_id' => $contract->id,
            'status' => EscrowStatus::Holding,
            'amount' => $contract->total_amount,
            'held_amount' => $contract->total_amount,
        ]);

        $result = $this->escrowService->refundFunds($contract);

        $this->assertInstanceOf(Escrow::class, $result);
        $this->assertEquals(EscrowStatus::Refunded, $result->status);
        $this->assertEquals($contract->total_amount, $result->refunded_amount);
    }
}
