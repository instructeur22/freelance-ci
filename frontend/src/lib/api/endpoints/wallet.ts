import { api } from "@/lib/api/client";
import type { Wallet, WalletTransaction, WithdrawalRequest } from "@/types/api";

export const walletApi = {
  get: () => api.get<Wallet>("/wallet"),

  transactions: () => api.list<WalletTransaction>("/wallet/transactions"),

  withdraw: (data: { amount: number; phone_number: string; withdrawal_method: string }) =>
    api.post<WithdrawalRequest>("/wallet/withdraw", data),
};
