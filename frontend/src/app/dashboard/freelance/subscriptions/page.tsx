"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { subscriptionApi } from "@/lib/api/endpoints/subscriptions";
import { formatPrice } from "@/lib/utils/format";
import Link from "next/link";

export default function SubscriptionsPage() {
  const queryClient = useQueryClient();

  const { data: plansData, isLoading } = useQuery({
    queryKey: ["subscription-plans"],
    queryFn: () => subscriptionApi.plans(),
  });

  const { data: currentSubData } = useQuery({
    queryKey: ["current-subscription"],
    queryFn: () => subscriptionApi.current(),
  });

  const cancelMutation = useMutation({
    mutationFn: () => subscriptionApi.cancel(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["current-subscription"] });
    },
  });

  const plans = plansData?.data ?? [];
  const currentSub = currentSubData?.data;
  const currentPlanId = currentSub?.plan_id;

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <span className="loading loading-spinner loading-lg text-primary" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Abonnements</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Choisissez le plan qui vous correspond
        </p>
      </div>

      {/* Current subscription */}
      {currentSub && (
        <div className="alert alert-info">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>
            Vous êtes actuellement abonné au plan{" "}
            <strong>{currentSub.status}</strong>.
            {currentSub.ends_at && (
              <> Expire le {new Date(currentSub.ends_at).toLocaleDateString("fr-FR")}.</>
            )}
          </span>
        </div>
      )}

      {/* Plans grid */}
      <div className="grid md:grid-cols-3 gap-4">
        {plans.map((plan) => {
          const isCurrentPlan = plan.id === currentPlanId;
          return (
            <div
              key={plan.id}
              className={`card bg-base-100 border-2 ${
                isCurrentPlan
                  ? "border-primary"
                  : "border-base-200"
              }`}
            >
              <div className="card-body p-6">
                <h3 className="card-title text-lg">{plan.name}</h3>
                <p className="text-sm text-base-content/60 mt-1">
                  {plan.description}
                </p>

                <div className="mt-4">
                  <p className="text-3xl font-bold">
                    {formatPrice(plan.price_monthly)}
                    <span className="text-sm font-normal text-base-content/50">
                      /mois
                    </span>
                  </p>
                  {plan.price_yearly > 0 && (
                    <p className="text-sm text-base-content/50">
                      {formatPrice(plan.price_yearly)}/an
                    </p>
                  )}
                </div>

                <ul className="space-y-2 mt-4">
                  {plan.features.map((feature, i) => (
                    <li key={i} className="flex items-center gap-2 text-sm">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-4 h-4 text-success flex-shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      {feature}
                    </li>
                  ))}
                </ul>

                <div className="card-actions mt-6">
                  {isCurrentPlan ? (
                    <button
                      onClick={() => cancelMutation.mutate()}
                      disabled={cancelMutation.isPending}
                      className="btn btn-outline w-full"
                    >
                      Résilier
                    </button>
                  ) : (
                    <Link
                      href={`/dashboard/freelance/subscriptions/purchase?plan_id=${plan.id}`}
                      className="btn btn-primary w-full"
                    >
                      {plan.price_monthly === 0 ? "Commencer" : "Souscrire"}
                    </Link>
                  )}
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
