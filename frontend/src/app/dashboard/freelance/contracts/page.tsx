"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { contractApi } from "@/lib/api/endpoints/contracts";
import { formatPrice, formatDate, getContractStatusLabel } from "@/lib/utils/format";
import Link from "next/link";

export default function FreelanceContractsPage() {
  const { data, isLoading } = useQuery({
    queryKey: ["contracts"],
    queryFn: () => contractApi.list(),
  });

  const contracts = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Mes contrats</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gérez vos missions en cours
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : contracts.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <p className="text-base-content/50">Aucun contrat pour le moment</p>
            <Link href="/projects" className="btn btn-primary btn-sm mt-4">
              Parcourir les projets
            </Link>
          </div>
        </div>
      ) : (
        <div className="space-y-3">
          {contracts.map((contract) => (
            <Link
              key={contract.id}
              href={`/dashboard/freelance/contracts/${contract.id}`}
              className="card bg-base-100 border border-base-200 hover:border-primary/30 transition-colors block"
            >
              <div className="card-body p-5">
                <div className="flex items-start justify-between gap-4">
                  <div className="flex-1 min-w-0">
                    <h3 className="font-semibold truncate">{contract.title}</h3>
                    {contract.client && (
                      <p className="text-sm text-base-content/60">
                        Client: {contract.client.first_name}{" "}
                        {contract.client.last_name}
                      </p>
                    )}
                  </div>
                  <div className="text-right">
                    <p className="font-bold text-primary">
                      {formatPrice(contract.freelance_amount)}
                    </p>
                    <span
                      className={`badge badge-sm mt-1 ${
                        contract.status === "active"
                          ? "badge-success"
                          : contract.status === "pending"
                            ? "badge-warning"
                            : contract.status === "completed"
                              ? "badge-info"
                              : "badge-ghost"
                      }`}
                    >
                      {getContractStatusLabel(contract.status)}
                    </span>
                  </div>
                </div>

                <div className="flex items-center gap-4 mt-3 text-xs text-base-content/50">
                  <span>Créé le {formatDate(contract.created_at)}</span>
                  {contract.milestones && (
                    <span>
                      {contract.milestones.filter((m) => m.is_completed).length}/
                      {contract.milestones.length} jalons
                    </span>
                  )}
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
