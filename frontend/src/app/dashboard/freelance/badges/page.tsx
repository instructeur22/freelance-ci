"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { badgeApi } from "@/lib/api/endpoints/monetization";
import { formatPrice, formatDate } from "@/lib/utils/format";

export default function BadgesPage() {
  const queryClient = useQueryClient();

  const { data: badgeData } = useQuery({
    queryKey: ["badge-status"],
    queryFn: () => badgeApi.status(),
  });

  const purchaseMutation = useMutation({
    mutationFn: () => badgeApi.purchase({ badge_type: "verified" }),
    onSuccess: (res) => {
      if (res.data.payment_url) {
        window.open(res.data.payment_url, "_blank");
      }
      queryClient.invalidateQueries({ queryKey: ["badge-status"] });
    },
  });

  const badge = badgeData?.data;
  const badgePrice = 10000; // 10 000 FCFA/year

  return (
    <div className="space-y-6 max-w-lg">
      <div>
        <h1 className="text-2xl font-bold">Badge vérifié</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Obtenez le badge vérifié pour augmenter votre crédibilité
        </p>
      </div>

      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-6 text-center">
          <div className={`w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center ${
            badge?.is_active ? "bg-primary text-primary-content" : "bg-base-200 text-base-content/30"
          }`}>
            <svg xmlns="http://www.w3.org/2000/svg" className="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
          </div>

          <h2 className="text-xl font-bold mb-2">
            {badge?.is_active ? "Badge actif" : "Badge vérifié"}
          </h2>
          <p className="text-sm text-base-content/70 mb-4">
            {badge?.is_active
              ? `Votre badge est actif jusqu'au ${formatDate(badge.expires_at)}`
              : "Montrez à vos clients que vous êtes un freelance de confiance"}
          </p>

          {badge?.is_active ? (
            <div className="badge badge-primary badge-lg gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
              </svg>
              Vérifié
            </div>
          ) : (
            <div className="space-y-4">
              <p className="text-2xl font-bold text-primary">
                {formatPrice(badgePrice)}
                <span className="text-sm font-normal text-base-content/50">/an</span>
              </p>
              <button
                onClick={() => purchaseMutation.mutate()}
                disabled={purchaseMutation.isPending}
                className="btn btn-primary w-full"
              >
                {purchaseMutation.isPending ? (
                  <span className="loading loading-spinner loading-sm" />
                ) : (
                  "Acheter le badge vérifié"
                )}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
