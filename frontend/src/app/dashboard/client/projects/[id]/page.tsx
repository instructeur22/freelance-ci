"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { projectApi } from "@/lib/api/endpoints/projects";
import { quoteApi } from "@/lib/api/endpoints/quotes";
import { formatPrice, formatDate } from "@/lib/utils/format";
import Link from "next/link";
import { useRouter } from "next/navigation";

export default function ClientProjectDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = params as unknown as { id: string };
  const queryClient = useQueryClient();
  const router = useRouter();

  const { data: projectData, isLoading } = useQuery({
    queryKey: ["project", id],
    queryFn: () => projectApi.detail(id),
  });

  const { data: quotesData } = useQuery({
    queryKey: ["project-quotes", id],
    queryFn: () => quoteApi.listForProject(id),
  });

  const acceptMutation = useMutation({
    mutationFn: (quoteId: string) => quoteApi.accept(quoteId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["project-quotes", id] });
      queryClient.invalidateQueries({ queryKey: ["contracts"] });
    },
  });

  const refuseMutation = useMutation({
    mutationFn: (quoteId: string) => quoteApi.refuse(quoteId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["project-quotes", id] });
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-12">
        <span className="loading loading-spinner loading-lg text-primary" />
      </div>
    );
  }

  const project = projectData?.data;
  const quotes = quotesData?.data ?? [];

  if (!project) {
    return (
      <div className="text-center py-12">
        <p>Projet introuvable</p>
        <Link href="/dashboard/client/projects" className="btn btn-ghost mt-4">
          Retour
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Link
          href="/dashboard/client/projects"
          className="btn btn-ghost btn-sm btn-square"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M15 19l-7-7 7-7"
            />
          </svg>
        </Link>
        <div>
          <h1 className="text-2xl font-bold">{project.title}</h1>
          <span
            className={`badge ${
              project.status === "open"
                ? "badge-success"
                : project.status === "in_progress"
                  ? "badge-info"
                  : "badge-ghost"
            }`}
          >
            {project.status === "open"
              ? "Ouvert"
              : project.status === "in_progress"
                ? "En cours"
                : "Terminé"}
          </span>
        </div>
      </div>

      {/* Project details */}
      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-5">
          <div className="prose max-w-none">
            <p className="whitespace-pre-wrap">{project.description}</p>
          </div>

          <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            {project.budget_min && (
              <div>
                <span className="text-xs text-base-content/50 uppercase">Budget</span>
                <p className="font-medium">
                  {formatPrice(project.budget_min)}
                  {project.budget_max && ` - ${formatPrice(project.budget_max)}`}
                </p>
              </div>
            )}
            {project.duration_days && (
              <div>
                <span className="text-xs text-base-content/50 uppercase">Durée</span>
                <p className="font-medium">{project.duration_days} jours</p>
              </div>
            )}
            <div>
              <span className="text-xs text-base-content/50 uppercase">Devis reçus</span>
              <p className="font-medium">{quotes.length}</p>
            </div>
            <div>
              <span className="text-xs text-base-content/50 uppercase">Publié le</span>
              <p className="font-medium">{formatDate(project.created_at)}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Quotes section */}
      <div>
        <h2 className="text-xl font-semibold mb-4">
          Devis reçus ({quotes.length})
        </h2>

        {quotes.length === 0 ? (
          <div className="card bg-base-100 border border-base-200">
            <div className="card-body items-center text-center py-8">
              <p className="text-base-content/50">
                Aucun devis reçu pour le moment
              </p>
            </div>
          </div>
        ) : (
          <div className="space-y-3">
            {quotes.map((quote) => (
              <div
                key={quote.id}
                className="card bg-base-100 border border-base-200"
              >
                <div className="card-body p-5">
                  <div className="flex items-start justify-between gap-4">
                    <div className="flex items-center gap-3">
                      <div className="avatar placeholder">
                        <div className="w-10 h-10 rounded-full bg-primary/10 text-primary text-sm font-medium flex items-center justify-center">
                          {quote.freelance?.first_name?.charAt(0)}
                          {quote.freelance?.last_name?.charAt(0)}
                        </div>
                      </div>
                      <div>
                        <p className="font-medium text-sm">
                          {quote.freelance
                            ? `${quote.freelance.first_name} ${quote.freelance.last_name}`
                            : "Freelance"}
                        </p>
                        <span
                          className={`badge badge-sm ${
                            quote.status === "pending"
                              ? "badge-warning"
                              : quote.status === "accepted"
                                ? "badge-success"
                                : "badge-ghost"
                          }`}
                        >
                          {quote.status === "pending"
                            ? "En attente"
                            : quote.status === "accepted"
                              ? "Accepté"
                              : "Refusé"}
                        </span>
                      </div>
                    </div>

                    <div className="text-right">
                      <p className="text-lg font-bold text-primary">
                        {formatPrice(quote.amount)}
                      </p>
                      {quote.estimated_duration && (
                        <p className="text-xs text-base-content/50">
                          {quote.estimated_duration} jours
                        </p>
                      )}
                    </div>
                  </div>

                  {quote.proposal && (
                    <p className="text-sm text-base-content/70 mt-2 line-clamp-3">
                      {quote.proposal}
                    </p>
                  )}

                  {quote.status === "pending" && (
                    <div className="flex gap-2 mt-3 pt-3 border-t border-base-200">
                      <button
                        onClick={() => acceptMutation.mutate(quote.id)}
                        disabled={acceptMutation.isPending}
                        className="btn btn-primary btn-sm"
                      >
                        {acceptMutation.isPending ? (
                          <span className="loading loading-spinner loading-xs" />
                        ) : (
                          "Accepter"
                        )}
                      </button>
                      <button
                        onClick={() => refuseMutation.mutate(quote.id)}
                        disabled={refuseMutation.isPending}
                        className="btn btn-ghost btn-sm"
                      >
                        Refuser
                      </button>
                    </div>
                  )}

                  {quote.status === "accepted" && (
                    <div className="mt-3 pt-3 border-t border-base-200">
                      <Link
                        href="/dashboard/client/contracts"
                        className="btn btn-success btn-sm"
                      >
                        Voir le contrat
                      </Link>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
