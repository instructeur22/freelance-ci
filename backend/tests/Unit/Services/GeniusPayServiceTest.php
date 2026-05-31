<?php

namespace Tests\Unit\Services;

use App\Services\GeniusPayService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeniusPayServiceTest extends TestCase
{
    private GeniusPayService $geniusPayService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geniusPayService = new GeniusPayService();
    }

    public function test_createTransaction_sends_request_and_returns_response(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([
                'transaction_id' => 'gp-tx-123',
                'reference' => 'ref-abc',
                'payment_url' => 'https://pay.geniuspay.com/tx/gp-tx-123',
                'status' => 'PENDING',
            ], 201),
        ]);

        $result = $this->geniusPayService->createTransaction([
            'amount' => 50000,
            'currency' => 'XOF',
            'description' => 'Test payment',
            'channel' => 'MOBILE_MONEY',
            'customer_email' => 'test@example.com',
        ]);

        $this->assertIsArray($result);
        $this->assertEquals('gp-tx-123', $result['transaction_id']);
        $this->assertEquals('ref-abc', $result['reference']);
        $this->assertEquals('PENDING', $result['status']);
    }

    public function test_createTransaction_throws_on_failure(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions' => Http::response([], 422),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $this->geniusPayService->createTransaction([
            'amount' => 50000,
            'currency' => 'XOF',
            'customer_email' => 'test@example.com',
        ]);
    }

    public function test_verifyWebhookSignature_validates_correctly(): void
    {
        $payload = ['transaction_id' => 'gp-123', 'status' => 'SUCCESS'];
        $secret = 'test-webhook-secret-for-test';
        config(['geniuspay.webhook_secret' => $secret]);
        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $result = $this->geniusPayService->verifyWebhookSignature($payload, $signature);

        $this->assertTrue($result);
    }

    public function test_verifyWebhookSignature_rejects_invalid(): void
    {
        $payload = ['transaction_id' => 'gp-123', 'status' => 'SUCCESS'];

        $result = $this->geniusPayService->verifyWebhookSignature($payload, 'invalid-signature');

        $this->assertFalse($result);
    }

    public function test_checkTransactionStatus_returns_status(): void
    {
        Http::fake([
            'api-sandbox.geniuspay.com/v1/transactions/gp-tx-123' => Http::response([
                'transaction_id' => 'gp-tx-123',
                'status' => 'SUCCESS',
            ]),
        ]);

        $result = $this->geniusPayService->checkTransactionStatus('gp-tx-123');

        $this->assertIsArray($result);
        $this->assertEquals('SUCCESS', $result['status']);
    }
}
