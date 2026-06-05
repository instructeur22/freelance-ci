"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { contractApi } from "@/lib/api/endpoints/contracts";
import { formatPrice, formatDate, getContractStatusLabel } from "@/lib/utils/format";
import Link from "next/link";

export default function FreelanceContractDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = params as unknown as { id: string };
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["contract", id],
    queryFn: () => contractApi.detail(id),
  });

  const signMutation = useMutation({
    mutationFn: () => contractApi.sign(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["contract", id] });
    },
  });

  const deliverMutation = useMutation({
    mutationFn: (milestoneId: string) =>
      contractApi.deliverMilestone(id, milestoneId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["contract", id] });
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <span className="loading loading-spinner loading-lg text-primary" />
      </div>
    );
  }

  const contract = data?.data;

  if (!contract) {
    return (
      <div className="text-center py-12">
        <p>Contrat introuvable</p>
        <Link href="/dashboard/freelance/contracts" className="btn btn-ghost mt-4">
          Retour
        </Link>
      </div>
    );
  }

  const milestones = contract.milestones ?? [];
  const totalMilestones = milestones.length;
  const validatedMilestones = milestones.filter((m) => m.validated_at).length;
  const progress = totalMilestones > 0 ? Math.round((validatedMilestones / totalMilestones) * 100) : 0;

  return (
    <div className="space-y-6 max-w-3xl">
      <Link
        href="/dashboard/freelance/contracts"
        className="text-sm text-base-content/60 hover:text-primary flex items-center gap-1"
      >
        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
        </svg>
        Retour aux contrats
      </Link>

      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-5">
          <div className="flex items-start justify-between">
            <div>
              <h1 className="text-xl font-bold">{contract.title}</h1>
              <span className={`badge mt-1 ${contract.status === "active" ? "badge-success" : contract.status === "pending" ? "badge-warning" : contract.status === "completed" ? "badge-info" : "badge-ghost"}`}>
                {getContractStatusLabel(contract.status)}
              </span>
            </div>
            <div className="text-right">
              <p className="text-2xl font-bold text-primary">
                {formatPrice(contract.freelance_amount)}
              </p>
              <p className="text-xs text-base-content/50">Montant net</p>
              {contract.platform_fee > 0 && (
                <p className="text-xs text-base-content/40">
                  Frais plateforme: {formatPrice(contract.platform_fee)}
                </p>
              )}
            </div>
          </div>

          {totalMilestones > 0 && (
            <div className="mt-4">
              <div className="flex justify-between text-sm mb-1">
                <span>Progression</span>
                <span>{validatedMilestones}/{totalMilestones} jalons</span>
              </div>
              <progress className="progress progress-primary w-full" value={progress} max="100" />
            </div>
          )}

          <div className="grid grid-cols-2 gap-4 mt-4">
            <div className="bg-base-200 rounded-lg p-3">
              <p className="text-xs text-base-content/50">Client</p>
              <p className="font-medium text-sm">
                {contract.client_signed_at
                  ? `Signé le ${formatDate(contract.client_signed_at)}`
                  : "En attente"}
              </p>
            </div>
            <div className="bg-base-200 rounded-lg p-3">
              <p className="text-xs text-base-content/50">Vous</p>
              <p className="font-medium text-sm">
                {contract.freelance_signed_at
                  ? `Signé le ${formatDate(contract.freelance_signed_at)}`
                  : "En attente de votre signature"}
              </p>
            </div>
          </div>

          {contract.status === "pending" && !contract.freelance_signed_at && (
            <button
              onClick={() => signMutation.mutate()}
              disabled={signMutation.isPending}
              className="btn btn-primary mt-4"
            >
              {signMutation.isPending ? (
                <span className="loading loading-spinner loading-sm" />
              ) : (
                "Signer le contrat"
              )}
            </button>
          )}
        </div>
      </div>

      {/* Milestones */}
      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-5">
          <h2 className="font-semibold mb-4">Jalons ({totalMilestones})</h2>

          {totalMilestones === 0 ? (
            <p className="text-sm text-base-content/50 text-center py-4">Aucun jalon défini</p>
          ) : (
            <div className="space-y-3">
              {milestones
                .sort((a, b) => a.sort_order - b.sort_order)
                .map((milestone, index) => (
                  <div
                    key={milestone.id}
                    className={`border rounded-lg p-4 ${
                      milestone.validated_at
                        ? "border-success bg-success/5"
                        : milestone.delivered_at
                          ? "border-info bg-info/5"
                          : "border-base-200"
                    }`}
                  >
                    <div className="flex items-start justify-between">
                      <div className="flex items-center gap-3">
                        <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                          milestone.validated_at
                            ? "bg-success text-success-content"
                            : milestone.delivered_at
                              ? "bg-info text-info-content"
                              : "bg-base-200 text-base-content/50"
                        }`}>
                          {milestone.validated_at ? "✓" : index + 1}
                        </div>
                        <div>
                          <h4 className="font-medium text-sm">{milestone.title}</h4>
                          {milestone.description && (
                            <p className="text-xs text-base-content/60">{milestone.description}</p>
                          )}
                        </div>
                      </div>
                      <div className="text-right">
                        <p className="font-semibold text-sm">{formatPrice(milestone.amount)}</p>
                        {milestone.due_date && (
                          <p className="text-xs text-base-content/50">{formatDate(milestone.due_date)}</p>
                        )}
                      </div>
                    </div>

                    <div className="flex items-center gap-2 mt-3">
                      {!milestone.delivered_at && !milestone.validated_at && (
                        <button
                          onClick={() => deliverMutation.mutate(milestone.id)}
                          disabled={deliverMutation.isPending}
                          className="btn btn-primary btn-xs"
                        >
                          Livrer
                        </button>
                      )}
                      {milestone.delivered_at && !milestone.validated_at && (
                        <span className="text-xs text-info">
                          Livré le {formatDate(milestone.delivered_at)} - En attente de validation
                        </span>
                      )}
                      {milestone.validated_at && (
                        <span className="text-xs text-success">
                          Validé le {formatDate(milestone.validated_at)} ✓
                        </span>
                      )}
                    </div>
                  </div>
                ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
