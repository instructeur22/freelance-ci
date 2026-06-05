"use client";

import { useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { boostApi } from "@/lib/api/endpoints/monetization";
import { formatPrice } from "@/lib/utils/format";

export default function BoostPage() {
  const queryClient = useQueryClient();
  const [target, setTarget] = useState<"profile" | "project">("profile");
  const [duration, setDuration] = useState<"7_days" | "30_days">("7_days");

  const { data: boostsData } = useQuery({
    queryKey: ["my-boosts"],
    queryFn: () => boostApi.list(),
  });

  const purchaseMutation = useMutation({
    mutationFn: () => boostApi.purchase({ target, duration }),
    onSuccess: (res) => {
      if (res.data.payment_url) {
        window.open(res.data.payment_url, "_blank");
      }
      queryClient.invalidateQueries({ queryKey: ["my-boosts"] });
    },
  });

  const boosts = boostsData?.data ?? [];
  const boostPrices: Record<string, number> = {
    "7_days": 5000,
    "30_days": 15000,
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Boost</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Augmentez votre visibilité sur la plateforme
        </p>
      </div>

      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-6 space-y-4">
          <h2 className="font-semibold">Configurer un boost</h2>

          <div className="form-control">
            <label className="label">
              <span className="label-text">Cible</span>
            </label>
            <div className="flex gap-3">
              <button
                onClick={() => setTarget("profile")}
                className={`btn flex-1 ${target === "profile" ? "btn-primary" : "btn-outline"}`}
              >
                Profil
              </button>
              <button
                onClick={() => setTarget("project")}
                className={`btn flex-1 ${target === "project" ? "btn-primary" : "btn-outline"}`}
              >
                Projet
              </button>
            </div>
          </div>

          <div className="form-control">
            <label className="label">
              <span className="label-text">Durée</span>
            </label>
            <div className="flex gap-3">
              <button
                onClick={() => setDuration("7_days")}
                className={`btn flex-1 ${duration === "7_days" ? "btn-primary" : "btn-outline"}`}
              >
                7 jours - {formatPrice(boostPrices["7_days"])}
              </button>
              <button
                onClick={() => setDuration("30_days")}
                className={`btn flex-1 ${duration === "30_days" ? "btn-primary" : "btn-outline"}`}
              >
                30 jours - {formatPrice(boostPrices["30_days"])}
              </button>
            </div>
          </div>

          <button
            onClick={() => purchaseMutation.mutate()}
            disabled={purchaseMutation.isPending}
            className="btn btn-primary w-full"
          >
            {purchaseMutation.isPending ? (
              <span className="loading loading-spinner loading-sm" />
            ) : (
              `Booster - ${formatPrice(boostPrices[duration])}`
            )}
          </button>
        </div>
      </div>

      {/* Active boosts */}
      {boosts.length > 0 && (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body p-5">
            <h2 className="font-semibold mb-4">Mes boosts actifs</h2>
            <div className="space-y-2">
              {boosts.filter((b) => b.is_active).map((boost) => (
                <div key={boost.id} className="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                  <div>
                    <p className="text-sm font-medium capitalize">
                      {boost.target === "profile" ? "Profil" : "Projet"} - {boost.duration === "7_days" ? "7 jours" : "30 jours"}
                    </p>
                    <p className="text-xs text-base-content/50">
                      Expire le {new Date(boost.ends_at).toLocaleDateString("fr-FR")}
                    </p>
                  </div>
                  <span className="badge badge-success badge-sm">Actif</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
