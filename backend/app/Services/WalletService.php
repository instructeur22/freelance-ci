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
            ["available_xof" => 0, "pending_xof" => 0, "total_earned_xof" => 0]
        );
    }

    public function getTransactions(User $user): LengthAwarePaginator
    {
        return WalletTransaction::where("wallet_id", $user->id)
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function requestWithdrawal(User $user, array $data): WithdrawalRequest|false
    {
        $wallet = $this->getWallet($user);
        $amount = (float) ($data["amount_xof"] ?? 0);
        if ($amount <= 0 || $amount > $wallet->available_xof) {
            return false;
        }
        return \DB::transaction(function () use ($user, $wallet, $amount, $data) {
            $wallet->decrement("available_xof", $amount);
            $wallet->increment("pending_xof", $amount);
            return WithdrawalRequest::create([
                "freelance_id" => $user->id,
                "amount_xof" => $amount,
                "withdrawal_method" => $data["withdrawal_method"],
                "phone_number" => $data["phone_number"] ?? null,
            ]);
        });
    }
}
