<?php
namespace App\Services;

use App\Enums\EscrowStatus;
use App\Enums\PaymentStatus;
use App\Models\Contract;
use App\Models\Escrow;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscrowService
{
    public function holdFunds(Contract $contract): Escrow
    {
        return DB::transaction(function () use ($contract) {
            $escrow = $contract->escrow;

            if (!$escrow) {
                $escrow = Escrow::create([
                    "contract_id" => $contract->id,
                    "amount" => $contract->amount,
                    "currency" => $contract->currency,
                    "status" => EscrowStatus::Holding,
                ]);
            }

            $clientWallet = Wallet::where("user_id", $contract->client_id)->firstOrFail();

            if ($clientWallet->balance < $contract->amount) {
                abort(400, "Solde insuffisant pour bloquer les fonds.");
            }

            $clientWallet->decrement("balance", $contract->amount);

            WalletTransaction::create([
                "wallet_id" => $clientWallet->id,
                "type" => "debit",
                "amount" => $contract->amount,
                "currency" => $contract->currency,
                "description" => "Fonds bloqu\u00e9s pour le contrat #{$contract->id}",
                "reference_type" => "contract",
                "reference_id" => $contract->id,
            ]);

            $contract->update(["payment_status" => PaymentStatus::Held]);

            return $escrow;
        });
    }

    public function releaseFunds(Contract $contract): Escrow
    {
        return DB::transaction(function () use ($contract) {
            $escrow = $contract->escrow;

            if (!$escrow || $escrow->status !== EscrowStatus::Holding) {
                abort(400, "Aucun fonds s\u00e9questr\u00e9 disponible.");
            }

            $freelanceWallet = Wallet::where("user_id", $contract->freelance_id)->firstOrFail();
            $freelanceWallet->increment("balance", $contract->amount);

            WalletTransaction::create([
                "wallet_id" => $freelanceWallet->id,
                "type" => "credit",
                "amount" => $contract->amount,
                "currency" => $contract->currency,
                "description" => "Fonds lib\u00e9r\u00e9s pour le contrat #{$contract->id}",
                "reference_type" => "contract",
                "reference_id" => $contract->id,
            ]);

            $escrow->update(["status" => EscrowStatus::Released, "released_at" => now()]);
            $contract->update(["payment_status" => PaymentStatus::Released]);

            return $escrow;
        });
    }

    public function refundFunds(Contract $contract): Escrow
    {
        return DB::transaction(function () use ($contract) {
            $escrow = $contract->escrow;

            if (!$escrow || $escrow->status !== EscrowStatus::Holding) {
                abort(400, "Aucun fonds s\u00e9questr\u00e9 \u00e0 rembourser.");
            }

            $clientWallet = Wallet::where("user_id", $contract->client_id)->firstOrFail();
            $clientWallet->increment("balance", $contract->amount);

            WalletTransaction::create([
                "wallet_id" => $clientWallet->id,
                "type" => "credit",
                "amount" => $contract->amount,
                "currency" => $contract->currency,
                "description" => "Remboursement des fonds pour le contrat #{$contract->id}",
                "reference_type" => "contract",
                "reference_id" => $contract->id,
            ]);

            $escrow->update(["status" => EscrowStatus::Refunded, "released_at" => now()]);
            $contract->update(["payment_status" => PaymentStatus::Refunded]);

            return $escrow;
        });
    }

    public function autoReleaseSchedule(): void
    {
        $contracts = Contract::whereHas("escrow", function ($q) {
            $q->where("status", EscrowStatus::Holding);
        })->where("status", "completed")->get();

        foreach ($contracts as $contract) {
            try {
                $this->releaseFunds($contract);
            } catch (\Exception $e) {
                Log::warning("Auto-release failed for contract {$contract->id}", [
                    "error" => $e->getMessage(),
                ]);
            }
        }
    }
}
