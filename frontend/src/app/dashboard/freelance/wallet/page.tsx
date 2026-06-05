"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { walletApi } from "@/lib/api/endpoints/wallet";
import { formatPrice, formatDate } from "@/lib/utils/format";
import { useState } from "react";

export default function WalletPage() {
  const queryClient = useQueryClient();
  const [showWithdrawModal, setShowWithdrawModal] = useState(false);
  const [withdrawForm, setWithdrawForm] = useState({
    amount: "",
    phone_number: "",
    withdrawal_method: "orange_money",
  });

  const { data: walletData, isLoading } = useQuery({
    queryKey: ["wallet"],
    queryFn: () => walletApi.get(),
  });

  const { data: txData } = useQuery({
    queryKey: ["wallet-transactions"],
    queryFn: () => walletApi.transactions(),
  });

  const withdrawMutation = useMutation({
    mutationFn: (data: { amount: number; phone_number: string; withdrawal_method: string }) =>
      walletApi.withdraw(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["wallet"] });
      setShowWithdrawModal(false);
      setWithdrawForm({ amount: "", phone_number: "", withdrawal_method: "orange_money" });
    },
  });

  const wallet = walletData?.data;
  const transactions = txData?.data ?? [];

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <span className="loading loading-spinner loading-lg text-primary" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Porte-monnaie</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gérez vos gains et demandes de retrait
        </p>
      </div>

      {/* Balance cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="card bg-primary text-primary-content">
          <div className="card-body p-5">
            <p className="text-sm opacity-80">Disponible</p>
            <p className="text-3xl font-bold">
              {wallet ? formatPrice(wallet.available_xof) : "0 FCFA"}
            </p>
          </div>
        </div>
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body p-5">
            <p className="text-sm text-base-content/60">En attente</p>
            <p className="text-2xl font-bold">
              {wallet ? formatPrice(wallet.pending_xof) : "0 FCFA"}
            </p>
          </div>
        </div>
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body p-5">
            <p className="text-sm text-base-content/60">Total gagné</p>
            <p className="text-2xl font-bold">
              {wallet ? formatPrice(wallet.total_earned_xof) : "0 FCFA"}
            </p>
          </div>
        </div>
      </div>

      {/* Withdraw button */}
      <button
        onClick={() => setShowWithdrawModal(true)}
        className="btn btn-primary"
        disabled={!wallet || wallet.available_xof <= 0}
      >
        Effectuer un retrait
      </button>

      {/* Transaction history */}
      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-5">
          <h2 className="font-semibold mb-4">Historique des transactions</h2>

          {transactions.length === 0 ? (
            <p className="text-sm text-base-content/50 text-center py-6">
              Aucune transaction
            </p>
          ) : (
            <div className="space-y-2">
              {transactions.map((tx) => (
                <div
                  key={tx.id}
                  className="flex items-center justify-between p-3 rounded-lg bg-base-200/50"
                >
                  <div className="flex items-center gap-3">
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center ${
                      tx.direction === "credit"
                        ? "bg-success/10 text-success"
                        : "bg-error/10 text-error"
                    }`}>
                      <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={tx.direction === "credit" ? "M12 4v16m8-8H4" : "M20 12H4"} />
                      </svg>
                    </div>
                    <div>
                      <p className="text-sm font-medium">{tx.description}</p>
                      <p className="text-xs text-base-content/50">{formatDate(tx.created_at)}</p>
                    </div>
                  </div>
                  <p className={`font-semibold text-sm ${tx.direction === "credit" ? "text-success" : "text-error"}`}>
                    {tx.direction === "credit" ? "+" : "-"}
                    {formatPrice(tx.amount_xof)}
                  </p>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Withdraw modal */}
      {showWithdrawModal && (
        <dialog className="modal modal-open">
          <div className="modal-box">
            <h3 className="font-bold text-lg mb-4">Demande de retrait</h3>
            <form
              onSubmit={(e) => {
                e.preventDefault();
                withdrawMutation.mutate({
                  amount: Number(withdrawForm.amount),
                  phone_number: withdrawForm.phone_number,
                  withdrawal_method: withdrawForm.withdrawal_method,
                });
              }}
              className="space-y-4"
            >
              <div className="form-control">
                <label className="label">
                  <span className="label-text">Montant (FCFA)</span>
                </label>
                <input
                  type="number"
                  value={withdrawForm.amount}
                  onChange={(e) =>
                    setWithdrawForm({ ...withdrawForm, amount: e.target.value })
                  }
                  max={wallet?.available_xof ?? 0}
                  className="input input-bordered"
                  required
                />
                {wallet && (
                  <label className="label">
                    <span className="label-text-alt text-base-content/50">
                      Maximum: {formatPrice(wallet.available_xof)}
                    </span>
                  </label>
                )}
              </div>

              <div className="form-control">
                <label className="label">
                  <span className="label-text">Numéro de téléphone</span>
                </label>
                <input
                  type="tel"
                  value={withdrawForm.phone_number}
                  onChange={(e) =>
                    setWithdrawForm({ ...withdrawForm, phone_number: e.target.value })
                  }
                  placeholder="+225 XX XX XX XX"
                  className="input input-bordered"
                  required
                />
              </div>

              <div className="form-control">
                <label className="label">
                  <span className="label-text">Méthode de retrait</span>
                </label>
                <select
                  value={withdrawForm.withdrawal_method}
                  onChange={(e) =>
                    setWithdrawForm({ ...withdrawForm, withdrawal_method: e.target.value })
                  }
                  className="select select-bordered"
                >
                  <option value="orange_money">Orange Money</option>
                  <option value="mtn_momo">MTN MoMo</option>
                  <option value="wave">Wave</option>
                  <option value="bank_transfer">Virement bancaire</option>
                </select>
              </div>

              <div className="modal-action">
                <button
                  type="button"
                  onClick={() => setShowWithdrawModal(false)}
                  className="btn btn-ghost"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  disabled={withdrawMutation.isPending}
                  className="btn btn-primary"
                >
                  {withdrawMutation.isPending ? (
                    <span className="loading loading-spinner loading-sm" />
                  ) : (
                    "Confirmer le retrait"
                  )}
                </button>
              </div>
            </form>
          </div>
          <form method="dialog" className="modal-backdrop">
            <button onClick={() => setShowWithdrawModal(false)}>close</button>
          </form>
        </dialog>
      )}
    </div>
  );
}
