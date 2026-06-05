"use client";

import { useState } from "react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { profileApi } from "@/lib/api/endpoints/profile";
import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/lib/api/endpoints/public";

export default function PortfolioPage() {
  const queryClient = useQueryClient();
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");

  const { data: profileData } = useQuery({
    queryKey: ["freelance-profile"],
    queryFn: () => profileApi.freelance(),
  });

  const portfolio = profileData?.data?.portfolio ?? [];

  const addMutation = useMutation({
    mutationFn: () =>
      profileApi.addPortfolio({
        title,
        description,
      } as any),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["freelance-profile"] });
      setTitle("");
      setDescription("");
    },
  });

  const removeMutation = useMutation({
    mutationFn: (id: string) => profileApi.removePortfolio(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["freelance-profile"] });
    },
  });

  return (
    <div className="space-y-6 max-w-2xl">
      <div>
        <h1 className="text-2xl font-bold">Portfolio</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Montrez vos réalisations aux clients
        </p>
      </div>

      {/* Add new */}
      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-5 space-y-3">
          <h2 className="font-semibold">Ajouter un projet</h2>
          <input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            placeholder="Titre du projet"
            className="input input-bordered"
          />
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="Description du projet"
            className="textarea textarea-bordered"
          />
          <button
            onClick={() => addMutation.mutate()}
            disabled={!title || addMutation.isPending}
            className="btn btn-primary"
          >
            Ajouter
          </button>
        </div>
      </div>

      {/* Portfolio list */}
      {portfolio.length > 0 && (
        <div className="grid gap-3">
          {portfolio.map((item) => (
            <div
              key={item.id}
              className="card bg-base-100 border border-base-200"
            >
              <div className="card-body p-4 flex-row items-start justify-between">
                <div>
                  <h3 className="font-medium">{item.title}</h3>
                  {item.description && (
                    <p className="text-sm text-base-content/70 mt-1">
                      {item.description}
                    </p>
                  )}
                </div>
                <button
                  onClick={() => removeMutation.mutate(item.id)}
                  className="btn btn-ghost btn-sm text-error"
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
          ))}
        </div>
      )}
    </div>
  );
}
