"use client";

import { useQuery } from "@tanstack/react-query";
import { quoteApi } from "@/lib/api/endpoints/quotes";
import { formatPrice, formatDate } from "@/lib/utils/format";
import Link from "next/link";

export default function FreelanceQuotesPage() {
  const { data, isLoading } = useQuery({
    queryKey: ["my-quotes"],
    queryFn: () => quoteApi.listForProject(""), // placeholder - will need actual endpoint
  });

  // For now show empty state
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Mes devis</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Suivez vos propositions envoyées
        </p>
      </div>

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
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
            />
          </svg>
          <h3 className="font-semibold mb-1">Aucun devis</h3>
          <p className="text-sm text-base-content/60 mb-4">
            Parcourez les projets disponibles et soumettez vos propositions
          </p>
          <Link href="/projects" className="btn btn-primary">
            Parcourir les projets
          </Link>
        </div>
      </div>
    </div>
  );
}
