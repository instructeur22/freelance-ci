"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { formatDate } from "@/lib/utils/format";
import { useState } from "react";

export default function AdminDisputesPage() {
  const queryClient = useQueryClient();
  const [adminNotes, setAdminNotes] = useState("");
  const [selectedDispute, setSelectedDispute] = useState<string | null>(null);

  const { data, isLoading } = useQuery({
    queryKey: ["admin-disputes"],
    queryFn: () => adminApi.disputes(),
  });

  const resolveMutation = useMutation({
    mutationFn: ({
      id,
      status,
      notes,
    }: {
      id: string;
      status: string;
      notes?: string;
    }) => adminApi.resolveDispute(id, { status, admin_notes: notes }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-disputes"] });
      setSelectedDispute(null);
      setAdminNotes("");
    },
  });

  const disputes = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Litiges</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gérez les litiges entre clients et freelances
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : disputes.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <p className="text-base-content/50">Aucun litige</p>
          </div>
        </div>
      ) : (
        <div className="space-y-3">
          {disputes.map((dispute) => (
            <div key={dispute.id} className="card bg-base-100 border border-base-200">
              <div className="card-body p-5">
                <div className="flex items-start justify-between">
                  <div>
                    <h3 className="font-medium">
                      Litige #{dispute.id.slice(0, 8)}
                    </h3>
                    <p className="text-sm text-base-content/60 mt-1">
                    Raison: {dispute.reason}
                    </p>
                    <p className="text-sm mt-2">{dispute.reason}</p>
                  </div>
                  <span
                    className={`badge badge-sm ${
                      dispute.status === "open"
                        ? "badge-error"
                        : dispute.status === "resolved"
                          ? "badge-success"
                          : "badge-ghost"
                    }`}
                  >
                    {dispute.status === "open"
                      ? "Ouvert"
                      : dispute.status === "resolved"
                        ? "Résolu"
                        : "Ignoré"}
                  </span>
                </div>

                <div className="text-xs text-base-content/50 mt-2">
                  Ouvert le {formatDate(dispute.created_at)}
                </div>

                {dispute.status === "open" && (
                  <div className="mt-3 space-y-2">
                    {selectedDispute === dispute.id ? (
                      <>
                        <textarea
                          value={adminNotes}
                          onChange={(e) => setAdminNotes(e.target.value)}
                          placeholder="Notes d'administration..."
                          className="textarea textarea-bordered w-full"
                        />
                        <div className="flex gap-2">
                          <button
                            onClick={() =>
                              resolveMutation.mutate({
                                id: dispute.id,
                                status: "resolved",
                                notes: adminNotes,
                              })
                            }
                            className="btn btn-success btn-sm"
                          >
                            Résoudre
                          </button>
                          <button
                            onClick={() => setSelectedDispute(null)}
                            className="btn btn-ghost btn-sm"
                          >
                            Annuler
                          </button>
                        </div>
                      </>
                    ) : (
                      <button
                        onClick={() => setSelectedDispute(dispute.id)}
                        className="btn btn-primary btn-sm"
                      >
                        Traiter
                      </button>
                    )}
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
