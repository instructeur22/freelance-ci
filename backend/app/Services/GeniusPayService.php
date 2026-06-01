<?php
namespace App\Services;

use App\Enums\GeniusPayStatus;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeniusPayService
{
    private readonly string $apiKey;
    private readonly string $apiSecret;
    private readonly string $mode;
    private readonly int $timeout;

    public function __construct()
    {
        $this->apiKey = config("geniuspay.api_key");
        $this->apiSecret = config("geniuspay.api_secret");
        $this->mode = config("geniuspay.mode");
        $this->timeout = config("geniuspay.timeout");
    }

    private function client(): PendingRequest
    {
        $baseUrl = $this->mode === "production"
            ? "https://api.geniuspay.com/v1"
            : "https://api-sandbox.geniuspay.com/v1";

        return Http::baseUrl($baseUrl)
            ->timeout($this->timeout)
            ->withHeaders([
                "X-API-Key" => $this->apiKey,
                "X-API-Secret" => $this->apiSecret,
                "Content-Type" => "application/json",
            ]);
    }

    public function createTransaction(array $data): array
    {
        $response = $this->client()->post("/transactions", $data);

        if ($response->failed()) {
            Log::error("GeniusPay transaction failed", [
                "status" => $response->status(),
                "body" => $response->body(),
            ]);
            $response->throw();
        }

        return $response->json();
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool
    {
        $secret = config("geniuspay.webhook_secret");
        $computed = hash_hmac("sha256", json_encode($payload), $secret);
        return hash_equals($computed, $signature);
    }

    public function checkTransactionStatus(string $transactionId): array
    {
        $response = $this->client()->get("/transactions/{$transactionId}");

        if ($response->failed()) {
            Log::error("GeniusPay status check failed", [
                "transaction_id" => $transactionId,
                "status" => $response->status(),
            ]);
            $response->throw();
        }

        return $response->json();
    }

    public function syncTransactions(): array
    {
        $pendingTransactions = \App\Models\Payment::where("operator_status", GeniusPayStatus::PENDING)
            ->where("created_at", "<", now()->subHours(24))
            ->get();

        $result = ["checked" => 0, "updated" => 0, "failed" => 0];

        foreach ($pendingTransactions as $transaction) {
            $result["checked"]++;
            try {
                $status = $this->checkTransactionStatus($transaction->operator_transaction_id);
                $transaction->update([
                    "operator_status" => $status["status"],
                    "metadata" => $status,
                ]);
                $result["updated"]++;
            } catch (\Exception $e) {
                Log::warning("Failed to sync transaction {$transaction->id}", [
                    "error" => $e->getMessage(),
                ]);
                $result["failed"]++;
            }
        }

        return $result;
    }
}
