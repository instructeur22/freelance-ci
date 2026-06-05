"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { formatDate } from "@/lib/utils/format";

export default function AdminBoostsPage() {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["admin-boosts"],
    queryFn: () => adminApi.boosts(),
  });

  const revokeMutation = useMutation({
    mutationFn: (id: string) => adminApi.revokeBoost(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-boosts"] });
    },
  });

  const boosts = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Boosts</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gestion des boosts
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : boosts.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <p className="text-base-content/50">Aucun boost</p>
          </div>
        </div>
      ) : (
        <div className="space-y-3">
          {boosts.map((boost) => (
            <div key={boost.id} className="card bg-base-100 border border-base-200">
              <div className="card-body p-5">
                <div className="flex items-start justify-between">
                  <div>
                    <h3 className="font-medium capitalize">
                      Boost {boost.target === "profile" ? "profil" : "projet"}
                    </h3>
                    <p className="text-sm text-base-content/60">
                      {boost.duration === "7_days" ? "7 jours" : "30 jours"}
                    </p>
                  </div>
                  <span
                    className={`badge badge-sm ${
                      boost.is_active ? "badge-success" : "badge-ghost"
                    }`}
                  >
                    {boost.is_active ? "Actif" : "Expiré"}
                  </span>
                </div>

                <div className="text-xs text-base-content/50 mt-2">
                  Du {formatDate(boost.started_at)} au{" "}
                  {formatDate(boost.ends_at)}
                </div>

                {boost.is_active && (
                  <button
                    onClick={() => revokeMutation.mutate(boost.id)}
                    disabled={revokeMutation.isPending}
                    className="btn btn-ghost btn-xs text-error mt-2"
                  >
                    Révoquer
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
