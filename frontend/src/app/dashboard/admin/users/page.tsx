"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { formatDate } from "@/lib/utils/format";
import Link from "next/link";

export default function AdminUsersPage() {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["admin-users"],
    queryFn: () => adminApi.users(),
  });

  const updateStatusMutation = useMutation({
    mutationFn: ({ id, status }: { id: string; status: string }) =>
      adminApi.updateUserStatus(id, status),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-users"] });
    },
  });

  const users = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Utilisateurs</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gérez les utilisateurs de la plateforme
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
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Inscrit le</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.map((user) => (
                <tr key={user.id}>
                  <td className="font-medium">
                    {user.first_name} {user.last_name}
                  </td>
                  <td className="text-sm">{user.email}</td>
                  <td>
                    <span className="badge badge-sm capitalize">
                      {user.role === "admin" ? "Admin" : user.role === "freelance" ? "Freelance" : "Client"}
                    </span>
                  </td>
                  <td>
                    <span
                      className={`badge badge-sm ${
                        user.status === "active"
                          ? "badge-success"
                          : user.status === "suspended"
                            ? "badge-warning"
                            : "badge-error"
                      }`}
                    >
                      {user.status === "active"
                        ? "Actif"
                        : user.status === "suspended"
                          ? "Suspendu"
                          : "Banni"}
                    </span>
                  </td>
                  <td className="text-sm text-base-content/60">
                    {formatDate(user.created_at)}
                  </td>
                  <td>
                    <div className="dropdown dropdown-end">
                      <button className="btn btn-ghost btn-xs">Actions</button>
                      <ul className="dropdown-content menu bg-base-100 rounded-box z-50 w-40 p-2 shadow-sm border border-base-200">
                        {user.status !== "suspended" && (
                          <li>
                            <button
                              onClick={() =>
                                updateStatusMutation.mutate({
                                  id: user.id,
                                  status: "suspended",
                                })
                              }
                            >
                              Suspendre
                            </button>
                          </li>
                        )}
                        {user.status !== "banned" && (
                          <li>
                            <button
                              onClick={() =>
                                updateStatusMutation.mutate({
                                  id: user.id,
                                  status: "banned",
                                })
                              }
                            >
                              Bannir
                            </button>
                          </li>
                        )}
                        {user.status !== "active" && (
                          <li>
                            <button
                              onClick={() =>
                                updateStatusMutation.mutate({
                                  id: user.id,
                                  status: "active",
                                })
                              }
                            >
                              Réactiver
                            </button>
                          </li>
                        )}
                      </ul>
                    </div>
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
