<?php

namespace Tests\Feature\Api;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
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

    public function test_can_initiate_payment(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-pay',
                'reference' => 'ref-pay',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-pay',
                'status' => 'PENDING',
            ], 201),
        ]);

        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/payments/initiate', [
                'amount' => 5000,
                'currency' => 'XOF',
                'description' => 'Test payment',
                'payment_channel' => 'MOBILE_MONEY',
                'transaction_type' => 'deposit',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['payment_url', 'transaction_id']]);
    }

    public function test_can_confirm_payment(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create([
            'legacy_user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'status' => PaymentStatus::Pending,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/payments/' . $payment->id . '/confirm', [
                'transaction_id' => 'gp-tx-confirm',
                'status' => 'SUCCESS',
            ]);

        $response->assertStatus(200);
    }

    public function test_can_list_payments(): void
    {
        $user = User::factory()->create();
        Payment::factory()->count(2)->create(['legacy_user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_show_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['legacy_user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/payments/' . $payment->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
