"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { formatDate } from "@/lib/utils/format";

export default function AdminVerificationsPage() {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["admin-verifications"],
    queryFn: () => adminApi.verifications(),
  });

  const approveMutation = useMutation({
    mutationFn: (id: string) => adminApi.approveVerification(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-verifications"] });
      queryClient.invalidateQueries({ queryKey: ["admin-dashboard"] });
    },
  });

  const rejectMutation = useMutation({
    mutationFn: (id: string) => adminApi.rejectVerification(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-verifications"] });
      queryClient.invalidateQueries({ queryKey: ["admin-dashboard"] });
    },
  });

  const verifications = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Vérifications</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Documents en attente de vérification
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : verifications.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <p className="text-base-content/50">Aucune vérification en attente</p>
          </div>
        </div>
      ) : (
        <div className="space-y-3">
          {verifications.map((v) => (
            <div key={v.id} className="card bg-base-100 border border-base-200">
              <div className="card-body p-5">
                <div className="flex items-start justify-between">
                  <div>
                    <h3 className="font-medium">
                      {v.user?.first_name} {v.user?.last_name}
                    </h3>
                    <p className="text-sm text-base-content/60">
                      Type: {v.type} | Soumis le {formatDate(v.created_at)}
                    </p>
                  </div>
                  <span className="badge badge-warning">{v.status}</span>
                </div>

                {v.document_url && (
                  <a
                    href={v.document_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="btn btn-outline btn-sm mt-3"
                  >
                    Voir le document
                  </a>
                )}

                {v.status === "pending" && (
                  <div className="flex gap-2 mt-3">
                    <button
                      onClick={() => approveMutation.mutate(v.id)}
                      disabled={approveMutation.isPending}
                      className="btn btn-success btn-sm"
                    >
                      Approuver
                    </button>
                    <button
                      onClick={() => rejectMutation.mutate(v.id)}
                      disabled={rejectMutation.isPending}
                      className="btn btn-ghost btn-sm"
                    >
                      Rejeter
                    </button>
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
