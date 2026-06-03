<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\FreelanceProfile;
use App\Models\GeniusPayWebhook;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\GeniusPayService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'geniuspay.api_key' => 'pk_test_key',
            'geniuspay.api_secret' => 'sk_test_secret',
            'geniuspay.webhook_secret' => 'whsec_test',
            'geniuspay.mode' => 'test',
        ]);

        $geniusPayService = new GeniusPayService();
        $this->paymentService = new PaymentService($geniusPayService);
    }

    public function test_initiate_creates_transaction_and_payment(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-123',
                'reference' => 'ref-abc',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-123',
                'status' => 'PENDING',
            ], 201),
        ]);

        $user = User::factory()->create();

        $result = $this->paymentService->initiate($user, [
            'amount' => 5000,
            'currency' => 'XOF',
            'description' => 'Verified Badge',
            'type' => TransactionType::BadgeVerified->value,
            'payment_channel' => 'MOBILE_MONEY',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment_url', $result);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
        ]);
    }

    public function test_confirm_payment_updates_status(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $result = $this->paymentService->confirm($user, $transaction->id, [
            'transaction_id' => 'gp-tx-123',
            'status' => 'SUCCESS',
        ]);

        $this->assertNotNull($result);
        $this->assertEquals(PaymentStatus::Released, $result->status);
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $transaction->id,
            'status' => PaymentStatus::Released->value,
        ]);
    }

    public function test_handleWebhook_processes_successful_payment(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id, 'available_xof' => 0]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'operator_transaction_id' => 'gp-tx-webhook-123',
        ]);
        $payment = Payment::factory()->create([
            'legacy_user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'status' => PaymentStatus::Pending,
            'amount' => 5000,
        ]);

        $payload = [
            'event' => 'payment.success',
            'transaction_id' => $transaction->id,
            'status' => 'SUCCESS',
            'amount' => 5000,
            'currency' => 'XOF',
            'channel' => 'MOBILE_MONEY',
            'operator' => 'ORANGE',
            'paid_at' => now()->toIso8601String(),
            'reference' => $transaction->id,
        ];
        $secret = config('geniuspay.webhook_secret');
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $request = Request::create('/api/webhooks/genius-pay', 'POST', $payload);
        $request->headers->set('X-Signature', $signature);

        $result = $this->paymentService->handleWebhook($request);

        $this->assertTrue($result);

        $this->assertDatabaseHas('payments', [
            'transaction_id' => $transaction->id,
            'status' => PaymentStatus::Released->value,
        ]);

        $this->assertDatabaseHas('genius_pay_webhooks', [
            'event_type' => 'payment.success',
            'is_processed' => true,
        ]);
    }

    public function test_handleWebhook_rejects_invalid_signature(): void
    {
        $payload = ['event' => 'payment.success', 'data' => ['transaction_id' => 'gp-tx-456']];
        $request = Request::create('/api/webhooks/genius-pay', 'POST', $payload);
        $request->headers->set('X-GeniusPay-Signature', 'invalid-signature');

        $result = $this->paymentService->handleWebhook($request);

        $this->assertFalse($result);
    }
}
