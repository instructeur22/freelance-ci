"use client";

import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/lib/api/endpoints/public";
import { formatPrice, formatDate } from "@/lib/utils/format";
import Link from "next/link";

export default function ProjectDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = params as unknown as { id: string };

  const { data, isLoading } = useQuery({
    queryKey: ["project", id],
    queryFn: () => publicApi.projectDetail(id),
  });

  if (isLoading) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="flex items-center justify-center min-h-[40vh]">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      </div>
    );
  }

  const project = data?.data;

  if (!project) {
    return (
      <div className="container mx-auto px-4 py-12 text-center">
        <p>Projet introuvable.</p>
        <Link href="/projects" className="btn btn-ghost mt-4">
          Retour aux projets
        </Link>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-12">
      <div className="mb-6">
        <Link
          href="/projects"
          className="text-sm text-base-content/60 hover:text-primary flex items-center gap-1"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-4 w-4"
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
          Retour aux projets
        </Link>
      </div>

      <div className="grid lg:grid-cols-3 gap-8">
        {/* Main content */}
        <div className="lg:col-span-2">
          <div className="card bg-base-100 border border-base-200">
            <div className="card-body p-6">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <h1 className="text-2xl font-bold">{project.title}</h1>
                  <div className="flex items-center gap-2 mt-2 flex-wrap">
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
                    {project.is_urgent && (
                      <span className="badge badge-error">Urgent</span>
                    )}
                    {project.is_remote && (
                      <span className="badge badge-outline">À distance</span>
                    )}
                  </div>
                </div>
              </div>

              <div className="divider" />

              <div className="prose max-w-none">
                <p className="whitespace-pre-wrap">{project.description}</p>
              </div>

              {/* Files */}
              {project.files && project.files.length > 0 && (
                <>
                  <div className="divider" />
                  <h3 className="font-semibold mb-3">Fichiers joints</h3>
                  <div className="flex flex-wrap gap-2">
                    {project.files.map((file) => (
                      <a
                        key={file.id}
                        href={file.file_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="btn btn-outline btn-sm"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          className="h-4 w-4"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                          />
                        </svg>
                        {file.file_name}
                      </a>
                    ))}
                  </div>
                </>
              )}
            </div>
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-4">
          <div className="card bg-base-100 border border-base-200">
            <div className="card-body p-5">
              <h3 className="font-semibold mb-4">Détails du projet</h3>

              <div className="space-y-3">
                <div>
                  <span className="text-xs text-base-content/50 uppercase tracking-wider">
                    Budget
                  </span>
                  <p className="font-medium text-lg text-primary">
                    {project.budget_min && project.budget_max
                      ? `${formatPrice(project.budget_min)} - ${formatPrice(project.budget_max)}`
                      : "Non spécifié"}
                  </p>
                </div>

                {project.category && (
                  <div>
                    <span className="text-xs text-base-content/50 uppercase tracking-wider">
                      Catégorie
                    </span>
                    <p>{project.category.name}</p>
                  </div>
                )}

                {project.duration_days && (
                  <div>
                    <span className="text-xs text-base-content/50 uppercase tracking-wider">
                      Durée estimée
                    </span>
                    <p>{project.duration_days} jours</p>
                  </div>
                )}

                {project.experience_level && (
                  <div>
                    <span className="text-xs text-base-content/50 uppercase tracking-wider">
                      Niveau d&apos;expérience
                    </span>
                    <p className="capitalize">{project.experience_level}</p>
                  </div>
                )}

                {project.location && (
                  <div>
                    <span className="text-xs text-base-content/50 uppercase tracking-wider">
                      Localisation
                    </span>
                    <p>{project.location}</p>
                  </div>
                )}

                <div>
                  <span className="text-xs text-base-content/50 uppercase tracking-wider">
                    Publié le
                  </span>
                  <p>{formatDate(project.published_at || project.created_at)}</p>
                </div>

                {project.deadline_at && (
                  <div>
                    <span className="text-xs text-base-content/50 uppercase tracking-wider">
                      Date limite
                    </span>
                    <p>{formatDate(project.deadline_at)}</p>
                  </div>
                )}

                <div className="flex gap-4 text-sm text-base-content/50">
                  <span>{project.quotes_count} devis</span>
                  <span>{project.views_count} vues</span>
                </div>
              </div>
            </div>
          </div>

          {/* CTA */}
          <Link
            href={`/auth/login?redirect=/projects/${project.id}`}
            className="btn btn-primary w-full"
          >
            Postuler à ce projet
          </Link>
        </div>
      </div>
    </div>
  );
}
