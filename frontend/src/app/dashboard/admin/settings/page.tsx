"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { useState } from "react";

export default function AdminSettingsPage() {
  const queryClient = useQueryClient();
  const [editingKey, setEditingKey] = useState<string | null>(null);
  const [editValue, setEditValue] = useState("");

  const { data, isLoading } = useQuery({
    queryKey: ["admin-settings"],
    queryFn: () => adminApi.settings(),
  });

  const updateMutation = useMutation({
    mutationFn: ({ key, value }: { key: string; value: string }) =>
      adminApi.updateSetting(key, value),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["admin-settings"] });
      setEditingKey(null);
    },
  });

  const settings = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Paramètres</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Configuration de la plateforme
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : (
        <div className="space-y-2">
          {settings.map((setting) => (
            <div
              key={setting.id}
              className="card bg-base-100 border border-base-200"
            >
              <div className="card-body p-4">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <h3 className="font-medium text-sm">{setting.key}</h3>
                    <p className="text-xs text-base-content/50">
                      {setting.description}
                    </p>
                  </div>

                  {editingKey === setting.key ? (
                    <div className="flex items-center gap-2">
                      <input
                        value={editValue}
                        onChange={(e) => setEditValue(e.target.value)}
                        className="input input-bordered input-sm w-40"
                      />
                      <button
                        onClick={() =>
                          updateMutation.mutate({
                            key: setting.key,
                            value: editValue,
                          })
                        }
                        disabled={updateMutation.isPending}
                        className="btn btn-primary btn-xs"
                      >
                        Sauver
                      </button>
                      <button
                        onClick={() => setEditingKey(null)}
                        className="btn btn-ghost btn-xs"
                      >
                        Annuler
                      </button>
                    </div>
                  ) : (
                    <div className="flex items-center gap-2">
                      <span className="text-sm font-mono bg-base-200 px-2 py-1 rounded">
                        {setting.value}
                      </span>
                      <button
                        onClick={() => {
                          setEditingKey(setting.key);
                          setEditValue(setting.value);
                        }}
                        className="btn btn-ghost btn-xs"
                      >
                        Modifier
                      </button>
                    </div>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
