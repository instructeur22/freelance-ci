<?php
namespace App\Services;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletService
{
    public function getWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ["user_id" => $user->id],
            ["balance" => 0, "pending_balance" => 0, "total_earned" => 0]
        );
    }

    public function getTransactions(User $user): LengthAwarePaginator
    {
        $wallet = $this->getWallet($user);
        return WalletTransaction::where("wallet_id", $wallet->id)
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function requestWithdrawal(User $user, array $data): WithdrawalRequest|false
    {
        $wallet = $this->getWallet($user);
        $amount = (float) ($data["amount"] ?? 0);
        if ($amount <= 0 || $amount > $wallet->balance) {
            return false;
        }
        return \DB::transaction(function () use ($user, $wallet, $amount, $data) {
            $wallet->decrement("balance", $amount);
            $wallet->increment("pending_balance", $amount);
            return WithdrawalRequest::create([
                "wallet_id" => $wallet->id,
                "user_id" => $user->id,
                "amount" => $amount,
                "method" => $data["method"],
                "account_identifier" => $data["account_identifier"] ?? null,
            ]);
        });
    }
}
