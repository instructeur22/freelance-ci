"use client";

import { useQuery } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { formatPrice, formatDate } from "@/lib/utils/format";

export default function AdminPaymentsPage() {
  const { data, isLoading } = useQuery({
    queryKey: ["admin-payments"],
    queryFn: () => adminApi.payments(),
  });

  const payments = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Paiements</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Monitoring des transactions
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
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
                      {payment.status}
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
