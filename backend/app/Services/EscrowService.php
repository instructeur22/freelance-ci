<?php
namespace App\Services;

use App\Enums\EscrowStatus;
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
                    "payment_id" => null,
                    "amount" => $contract->total_amount,
                    "status" => EscrowStatus::Holding,
                ]);
            }

            $clientWallet = Wallet::where("user_id", $contract->client_id)->firstOrFail();

            if ($clientWallet->balance < $contract->total_amount) {
                abort(400, "Solde insuffisant pour bloquer les fonds.");
            }

            $clientWallet->decrement("balance", $contract->total_amount);

            WalletTransaction::create([
                "wallet_id" => $clientWallet->id,
                "type" => "debit",
                "amount" => $contract->total_amount,
                "description" => "Fonds bloqu\u00e9s pour le contrat #{$contract->id}",
                "reference" => "contract:{$contract->id}",
            ]);

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
            $freelanceWallet->increment("balance", $contract->total_amount);

            WalletTransaction::create([
                "wallet_id" => $freelanceWallet->id,
                "type" => "credit",
                "amount" => $contract->total_amount,
                "description" => "Fonds lib\u00e9r\u00e9s pour le contrat #{$contract->id}",
                "reference" => "contract:{$contract->id}",
            ]);

            $escrow->update(["status" => EscrowStatus::Released, "released_at" => now()]);

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
            $clientWallet->increment("balance", $contract->total_amount);

            WalletTransaction::create([
                "wallet_id" => $clientWallet->id,
                "type" => "credit",
                "amount" => $contract->total_amount,
                "description" => "Remboursement des fonds pour le contrat #{$contract->id}",
                "reference" => "contract:{$contract->id}",
            ]);

            $escrow->update(["status" => EscrowStatus::Refunded, "refunded_at" => now()]);

            return $escrow;
        });
    }

    public function autoReleaseSchedule(): int
    {
        $contracts = Contract::whereHas("escrow", function ($q) {
            $q->where("status", EscrowStatus::Holding);
        })->where("status", "completed")->get();

        $count = 0;

        foreach ($contracts as $contract) {
            try {
                $this->releaseFunds($contract);
                $count++;
            } catch (\Exception $e) {
                Log::warning("Auto-release failed for contract {$contract->id}", [
                    "error" => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }
}
