"use client";

import { useQuery } from "@tanstack/react-query";
import { paymentApi } from "@/lib/api/endpoints/payments";
import { formatPrice, formatDate } from "@/lib/utils/format";
import Link from "next/link";

export default function PaymentStatusPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = params as unknown as { id: string };

  const { data, isLoading, refetch } = useQuery({
    queryKey: ["payment", id],
    queryFn: () => paymentApi.detail(id),
    refetchInterval: 5000, // Poll every 5s
  });

  const payment = data?.data;

  if (isLoading) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <span className="loading loading-spinner loading-lg text-primary" />
      </div>
    );
  }

  if (!payment) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <div className="text-center">
          <p className="text-base-content/50">Paiement introuvable</p>
          <Link href="/dashboard" className="btn btn-ghost mt-4">
            Retour
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-[60vh] flex items-center justify-center px-4">
      <div className="max-w-md w-full">
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center p-8">
            {/* Status icon */}
            <div
              className={`w-20 h-20 rounded-full flex items-center justify-center mb-6 ${
                payment.status === "completed"
                  ? "bg-success/10 text-success"
                  : payment.status === "pending"
                    ? "bg-warning/10 text-warning"
                    : payment.status === "failed"
                      ? "bg-error/10 text-error"
                      : "bg-base-200 text-base-content/30"
              }`}
            >
              {payment.status === "completed" ? (
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="w-10 h-10"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              ) : payment.status === "pending" ? (
                <span className="loading loading-spinner loading-lg" />
              ) : (
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  className="w-10 h-10"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"
                  />
                </svg>
              )}
            </div>

            <h1 className="text-2xl font-bold mb-2">
              {payment.status === "completed"
                ? "Paiement réussi"
                : payment.status === "pending"
                  ? "Paiement en cours"
                  : "Paiement échoué"}
            </h1>

            <p className="text-3xl font-bold text-primary mt-4">
              {formatPrice(payment.amount)}
            </p>

            <div className="divider" />

            <div className="w-full space-y-3 text-left">
              <div className="flex justify-between text-sm">
                <span className="text-base-content/60">Référence</span>
                <span className="font-mono text-xs">
                  {payment.reference || payment.id.slice(0, 12)}
                </span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-base-content/60">Statut</span>
                <span
                  className={`badge badge-sm ${
                    payment.status === "completed"
                      ? "badge-success"
                      : payment.status === "pending"
                        ? "badge-warning"
                        : "badge-error"
                  }`}
                >
                  {payment.status === "completed"
                    ? "Payé"
                    : payment.status === "pending"
                      ? "En attente"
                      : "Échoué"}
                </span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-base-content/60">Canal</span>
                <span>{payment.payment_channel || "-"}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-base-content/60">Date</span>
                <span>{formatDate(payment.created_at)}</span>
              </div>
            </div>

            {payment.status === "pending" && (
              <div className="mt-6">
                <div className="alert alert-info text-sm">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <span>
                    En attente de confirmation. La page se met à jour
                    automatiquement.
                  </span>
                </div>
                <button
                  onClick={() => refetch()}
                  className="btn btn-outline btn-sm mt-3"
                >
                  Rafraîchir
                </button>
              </div>
            )}

            <div className="flex gap-3 mt-8">
              <Link href="/dashboard" className="btn btn-primary">
                Tableau de bord
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
