<?php
namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\FreelanceProfile;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private readonly GeniusPayService $geniusPayService,
    ) {}

    public function initiate(User $user, array $data): ?array
    {
        $data["user_id"] = $user->id;
        try {
            return $this->initiatePayment($data);
        } catch (\Exception $e) {
            Log::error("Payment initiation failed", ["error" => $e->getMessage()]);
            return null;
        }
    }

    public function confirm(User $user, string $id, array $data): ?Payment
    {
        try {
            return $this->confirmPayment($id, $data);
        } catch (\Exception $e) {
            Log::error("Payment confirmation failed", ["error" => $e->getMessage()]);
            return null;
        }
    }

    public function find(User $user, string $id): ?Payment
    {
        return $user->payments()->find($id);
    }

    public function listForUser(User $user): LengthAwarePaginator
    {
        return $user->payments()
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function handleWebhook(Request $request): bool
    {
        try {
            $payload = $request->all();
            $signature = $request->header("X-Signature", "");

            if (!$this->geniusPayService->verifyWebhookSignature($payload, $signature)) {
                Log::warning("Genius Pay webhook invalid signature");
                return false;
            }

            $transactionId = $payload["transaction_id"] ?? null;
            if ($transactionId) {
                $this->confirmPayment($transactionId, $payload);
            }

            \App\Models\GeniusPayWebhook::create([
                "event_type" => $payload["event"] ?? "unknown",
                "payload" => $payload,
                "signature" => $signature,
                "is_processed" => true,
                "processed_at" => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Webhook handling failed", ["error" => $e->getMessage()]);
            return false;
        }
    }

    public function initiatePayment(array $data): array
    {
        $user = User::findOrFail($data["user_id"]);

        $transaction = DB::transaction(function () use ($user, $data) {
            $transaction = Transaction::create([
                "user_id" => $user->id,
                "type" => $data["type"] ?? TransactionType::Mission,
                "amount" => $data["amount"],
                "currency" => $data["currency"] ?? "XOF",
                "description" => $data["description"] ?? "",
                "payment_channel" => $data["payment_channel"],
                "payment_operator" => $data["payment_operator"] ?? null,
                "operator_status" => "PENDING",
                "metadata" => $data["metadata"] ?? [],
            ]);

            $geniusPayload = [
                "amount" => $data["amount"],
                "currency" => $data["currency"] ?? "XOF",
                "description" => $data["description"] ?? "",
                "channel" => $data["payment_channel"],
                "operator" => $data["payment_operator"] ?? null,
                "customer_email" => $user->email,
                "customer_name" => $user->name ?? $user->email,
                "transaction_id" => (string) $transaction->id,
                "callback_url" => route("api.v1.payments.callback"),
            ];

            $response = $this->geniusPayService->createTransaction($geniusPayload);

            $transaction->update([
                "operator_transaction_id" => $response["transaction_id"] ?? null,
                "operator_reference" => $response["reference"] ?? null,
                "payment_url" => $response["payment_url"] ?? null,
            ]);

            return $transaction;
        });

        return [
            "transaction" => $transaction,
            "payment_url" => $transaction->payment_url,
            "reference" => $transaction->operator_reference,
        ];
    }

    public function confirmPayment(string $paymentId, array $geniusResponse): Payment
    {
        $transaction = Transaction::findOrFail($paymentId);

        $status = $geniusResponse["status"] ?? "FAILED";
        $operatorStatus = match ($status) {
            "SUCCESS" => PaymentStatus::Released,
            "FAILED" => PaymentStatus::Failed,
            "CANCELLED" => PaymentStatus::Cancelled,
            default => PaymentStatus::Pending,
        };

        $payment = DB::transaction(function () use ($transaction, $operatorStatus, $geniusResponse) {
            $transaction->update([
                "operator_status" => $geniusResponse["status"] ?? "FAILED",
                "metadata" => array_merge($transaction->metadata ?? [], ["callback" => $geniusResponse]),
                "paid_at" => $operatorStatus === PaymentStatus::Released ? now() : null,
            ]);

            $payment = Payment::create([
                "transaction_id" => $transaction->id,
                "user_id" => $transaction->user_id,
                "amount" => $transaction->amount,
                "currency" => $transaction->currency,
                "status" => $operatorStatus,
                "transaction_type" => $transaction->type,
                "channel" => $geniusResponse["channel"] ?? null,
                "operator" => $geniusResponse["operator"] ?? null,
                "reference" => $geniusResponse["reference"] ?? null,
                "metadata" => $geniusResponse,
            ]);

            if ($operatorStatus === PaymentStatus::Released) {
                $this->handlePostPaymentActions($transaction);
                $this->checkReferralCompletion($transaction);
            }

            return $payment;
        });

        return $payment;
    }

    private function handlePostPaymentActions(Transaction $transaction): void
    {
        match ($transaction->type) {
            TransactionType::BadgeVerified => $this->activateBadge($transaction),
            TransactionType::BoostProfile, TransactionType::BoostProject => $this->activateBoost($transaction),
            TransactionType::Subscription => $this->activateSubscription($transaction),
            default => null,
        };
    }

    private function activateBadge(Transaction $transaction): void
    {
        $metadata = $transaction->metadata ?? [];
        $profileId = $metadata["freelance_profile_id"] ?? null;

        if (!$profileId) {
            Log::warning("Badge activation missing freelance_profile_id", [
                "transaction_id" => $transaction->id,
            ]);
            return;
        }

        $profile = FreelanceProfile::find($profileId);
        if (!$profile) {
            Log::warning("Badge activation profile not found", [
                "freelance_profile_id" => $profileId,
            ]);
            return;
        }

        app(BadgeService::class)->activate(
            $profile,
            null,
            (int) $transaction->amount
        );
    }

    private function activateSubscription(Transaction $transaction): void
    {
        $metadata = $transaction->metadata ?? [];
        $profileId = $metadata["freelance_profile_id"] ?? null;
        $planId = $metadata["plan_id"] ?? null;
        $billingCycle = $metadata["billing_cycle"] ?? "monthly";

        if (!$profileId || !$planId) {
            Log::warning("Subscription activation missing required metadata", [
                "transaction_id" => $transaction->id,
                "metadata" => $metadata,
            ]);
            return;
        }

        $profile = FreelanceProfile::find($profileId);
        if (!$profile) {
            Log::warning("Subscription activation profile not found", [
                "freelance_profile_id" => $profileId,
            ]);
            return;
        }

        app(SubscriptionService::class)->activate(
            $profile,
            $planId,
            $billingCycle,
            (int) $transaction->amount
        );
    }

    private function activateBoost(Transaction $transaction): void
    {
        $metadata = $transaction->metadata ?? [];
        $profileId = $metadata["freelance_profile_id"] ?? null;
        $targetType = $metadata["target_type"] ?? null;
        $targetId = $metadata["target_id"] ?? null;
        $duration = $metadata["duration"] ?? null;

        if (!$profileId || !$targetType || !$duration) {
            Log::warning("Boost activation missing required metadata", [
                "transaction_id" => $transaction->id,
                "metadata" => $metadata,
            ]);
            return;
        }

        $profile = FreelanceProfile::find($profileId);
        if (!$profile) {
            Log::warning("Boost activation profile not found", [
                "freelance_profile_id" => $profileId,
            ]);
            return;
        }

        app(BoostService::class)->activate(
            $profile,
            $targetType,
            $targetType === "profile" ? null : $targetId,
            $duration,
            (int) $transaction->amount
        );
    }

    private function checkReferralCompletion(Transaction $transaction): void
    {
        if ($transaction->type !== TransactionType::Mission) {
            return;
        }

        $referredUser = User::find($transaction->user_id);
        if (!$referredUser) {
            return;
        }

        $completedPayments = Payment::where("user_id", $referredUser->id)
            ->where("status", PaymentStatus::Released)
            ->count();

        if ($completedPayments === 1) {
            app(ReferralService::class)->completeReferral($referredUser);
        }
    }

    public function getPaymentStatus(string $transactionId): array
    {
        $transaction = Transaction::with("payments")->findOrFail($transactionId);

        return [
            "transaction" => $transaction,
            "payments" => $transaction->payments,
            "operator_status" => $transaction->operator_status,
        ];
    }
}
