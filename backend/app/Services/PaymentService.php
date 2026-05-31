<?php
namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Escrow;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly GeniusPayService $geniusPayService,
    ) {}

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

        DB::transaction(function () use ($transaction, $operatorStatus, $geniusResponse) {
            $transaction->update([
                "operator_status" => $geniusResponse["status"] ?? "FAILED",
                "metadata" => array_merge($transaction->metadata ?? [], ["callback" => $geniusResponse]),
                "paid_at" => $operatorStatus === PaymentStatus::Released ? now() : null,
            ]);

            Payment::create([
                "transaction_id" => $transaction->id,
                "user_id" => $transaction->user_id,
                "amount" => $transaction->amount,
                "currency" => $transaction->currency,
                "status" => $operatorStatus,
                "payment_method" => $geniusResponse["channel"] ?? null,
                "operator" => $geniusResponse["operator"] ?? null,
                "operator_reference" => $geniusResponse["reference"] ?? null,
                "metadata" => $geniusResponse,
            ]);
        });

        return Payment::where("transaction_id", $transaction->id)->latest()->first();
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
