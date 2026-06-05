"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { formatDate } from "@/lib/utils/format";

export default function AdminReportsPage() {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["admin-reports"],
    queryFn: () => adminApi.reports(),
  });

  const resolveMutation = useMutation({
    mutationFn: (id: string) => adminApi.resolveReport(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-reports"] });
    },
  });

  const reports = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Signalements</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gérez les signalements utilisateurs
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : reports.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <p className="text-base-content/50">Aucun signalement</p>
          </div>
        </div>
      ) : (
        <div className="space-y-3">
          {reports.map((report) => (
            <div key={report.id} className="card bg-base-100 border border-base-200">
              <div className="card-body p-5">
                <div className="flex items-start justify-between">
                  <div>
                    <h3 className="font-medium">Signalement #{report.id.slice(0, 8)}</h3>
                    <p className="text-sm text-base-content/60 mt-1">
                      Type: {report.type}
                    </p>
                    <p className="text-sm mt-2">{report.description}</p>
                  </div>
                  <span className={`badge badge-sm ${
                    report.status === "open"
                      ? "badge-error"
                      : report.status === "resolved"
                        ? "badge-success"
                        : "badge-ghost"
                  }`}>
                    {report.status === "open"
                      ? "Ouvert"
                      : report.status === "resolved"
                        ? "Résolu"
                        : "Ignoré"}
                  </span>
                </div>

                <div className="text-xs text-base-content/50 mt-2">
                  Signalé le {formatDate(report.created_at)}
                </div>

                {report.status === "open" && (
                  <button
                    onClick={() => resolveMutation.mutate(report.id)}
                    disabled={resolveMutation.isPending}
                    className="btn btn-success btn-sm mt-3"
                  >
                    Marquer comme résolu
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
