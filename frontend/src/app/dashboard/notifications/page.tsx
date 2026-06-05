"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { notificationApi } from "@/lib/api/endpoints/notifications";
import { formatRelativeDate } from "@/lib/utils/format";
import Link from "next/link";

export default function NotificationsPage() {
  const queryClient = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ["notifications"],
    queryFn: () => notificationApi.list(),
  });

  const markReadMutation = useMutation({
    mutationFn: (id: string) => notificationApi.markRead(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["notifications"] });
    },
  });

  const markAllReadMutation = useMutation({
    mutationFn: () => notificationApi.markAllRead(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["notifications"] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => notificationApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["notifications"] });
    },
  });

  const notifications = data?.data ?? [];

  return (
    <div className="max-w-2xl space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Notifications</h1>
          <p className="text-base-content/70 text-sm mt-1">
            Restez informé de vos activités
          </p>
        </div>
        {notifications.some((n) => !n.is_read) && (
          <button
            onClick={() => markAllReadMutation.mutate()}
            disabled={markAllReadMutation.isPending}
            className="btn btn-outline btn-sm"
          >
            Tout marquer comme lu
          </button>
        )}
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : notifications.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="w-16 h-16 text-base-content/20 mb-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={1.5}
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
              />
            </svg>
            <p className="text-base-content/50">Aucune notification</p>
          </div>
        </div>
      ) : (
        <div className="space-y-2">
          {notifications.map((n) => (
            <div
              key={n.id}
              className={`card border ${
                n.is_read ? "border-base-200" : "border-primary/30 bg-primary/5"
              }`}
            >
              <div className="card-body p-4">
                <div className="flex items-start justify-between gap-4">
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      {!n.is_read && (
                        <span className="w-2 h-2 rounded-full bg-primary flex-shrink-0" />
                      )}
                      <h3
                        className={`text-sm ${
                          n.is_read ? "font-normal" : "font-semibold"
                        }`}
                      >
                        {n.title}
                      </h3>
                    </div>
                    {n.body && (
                      <p className="text-xs text-base-content/60 mt-1">
                        {n.body}
                      </p>
                    )}
                    <p className="text-xs text-base-content/40 mt-1">
                      {formatRelativeDate(n.created_at)}
                    </p>
                  </div>
                  <div className="flex items-center gap-1 flex-shrink-0">
                    {!n.is_read && (
                      <button
                        onClick={() => markReadMutation.mutate(n.id)}
                        className="btn btn-ghost btn-xs"
                        title="Marquer comme lu"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          className="w-4 h-4"
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
                      </button>
                    )}
                    <button
                      onClick={() => deleteMutation.mutate(n.id)}
                      className="btn btn-ghost btn-xs text-error"
                      title="Supprimer"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
                {n.action_url && (
                  <div className="mt-2">
                    <Link
                      href={n.action_url}
                      className="link link-primary text-xs"
                    >
                      Voir les détails
                    </Link>
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
