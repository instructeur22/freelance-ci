"use client";

import { useQuery } from "@tanstack/react-query";
import { paymentApi } from "@/lib/api/endpoints/payments";
import { formatPrice, formatDate } from "@/lib/utils/format";

export default function ClientPaymentsPage() {
  const { data, isLoading } = useQuery({
    queryKey: ["payments"],
    queryFn: () => paymentApi.list(),
  });

  const payments = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Historique des paiements</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Suivez tous vos paiements effectués
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : payments.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <p className="text-base-content/50">Aucun paiement pour le moment</p>
          </div>
        </div>
      ) : (
        <div className="overflow-x-auto">
          <table className="table">
            <thead>
              <tr>
                <th>Référence</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Canal</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              {payments.map((payment) => (
                <tr key={payment.id}>
                  <td className="text-sm font-mono">
                    {payment.reference || payment.id.slice(0, 8)}
                  </td>
                  <td className="font-medium">
                    {formatPrice(payment.amount)}
                  </td>
                  <td>
                    <span
                      className={`badge badge-sm ${
                        payment.status === "completed"
                          ? "badge-success"
                          : payment.status === "pending"
                            ? "badge-warning"
                            : payment.status === "failed"
                              ? "badge-error"
                              : "badge-ghost"
                      }`}
                    >
                      {payment.status === "completed"
                        ? "Payé"
                        : payment.status === "pending"
                          ? "En attente"
                          : payment.status === "failed"
                            ? "Échoué"
                            : payment.status}
                    </span>
                  </td>
                  <td className="text-sm">
                    {payment.payment_channel || "-"}
                  </td>
                  <td className="text-sm text-base-content/60">
                    {formatDate(payment.created_at)}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
