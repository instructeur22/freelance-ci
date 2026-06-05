"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { formatDate } from "@/lib/utils/format";

export default function AdminBadgesPage() {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["admin-badges"],
    queryFn: () => adminApi.badges(),
  });

  const revokeMutation = useMutation({
    mutationFn: (id: string) => adminApi.revokeBadge(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-badges"] });
    },
  });

  const badges = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Badges vérifiés</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gestion des badges attribués
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : badges.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <p className="text-base-content/50">Aucun badge attribué</p>
          </div>
        </div>
      ) : (
        <div className="overflow-x-auto">
          <table className="table">
            <thead>
              <tr>
                <th>Freelance</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Attribué le</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {badges.map((badge) => (
                <tr key={badge.id}>
                  <td className="font-medium">
                    {badge.freelance_profile_id.slice(0, 8)}
                  </td>
                  <td className="text-sm">{badge.badge_type}</td>
                  <td>
                    <span
                      className={`badge badge-sm ${
                        badge.is_active ? "badge-success" : "badge-ghost"
                      }`}
                    >
                      {badge.is_active ? "Actif" : "Inactif"}
                    </span>
                  </td>
                  <td className="text-sm text-base-content/60">
                    {formatDate(badge.granted_at)}
                  </td>
                  <td>
                    {badge.is_active && (
                      <button
                        onClick={() => revokeMutation.mutate(badge.id)}
                        disabled={revokeMutation.isPending}
                        className="btn btn-ghost btn-xs text-error"
                      >
                        Révoquer
                      </button>
                    )}
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
