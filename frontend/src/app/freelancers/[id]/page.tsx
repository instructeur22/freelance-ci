"use client";

import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/lib/api/endpoints/public";
import {
  formatPrice,
  getExperienceLabel,
  formatDate,
} from "@/lib/utils/format";
import Link from "next/link";

export default function FreelancerDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = params as unknown as { id: string };

  const { data, isLoading } = useQuery({
    queryKey: ["freelancer", id],
    queryFn: () => publicApi.freelanceDetail(id),
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

  const freelance = data?.data;

  if (!freelance) {
    return (
      <div className="container mx-auto px-4 py-12 text-center">
        <p>Freelance introuvable.</p>
        <Link href="/freelancers" className="btn btn-ghost mt-4">
          Retour aux freelances
        </Link>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-12">
      <div className="mb-6">
        <Link
          href="/freelancers"
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
          Retour aux freelances
        </Link>
      </div>

      <div className="grid lg:grid-cols-3 gap-8">
        {/* Main - Profile */}
        <div className="lg:col-span-2 space-y-6">
          {/* Profile header */}
          <div className="card bg-base-100 border border-base-200">
            <div className="card-body p-6">
              <div className="flex items-start gap-4">
                <div className="avatar placeholder">
                  <div className="w-20 h-20 rounded-full bg-primary text-primary-content">
                    <span className="text-2xl font-medium">
                      {freelance.professional_title?.charAt(0) || "F"}
                    </span>
                  </div>
                </div>
                <div className="flex-1">
                  <h1 className="text-2xl font-bold">
                    {freelance.professional_title || "Freelance"}
                  </h1>
                  {freelance.tagline && (
                    <p className="text-base-content/70 mt-1">
                      {freelance.tagline}
                    </p>
                  )}
                  <div className="flex items-center gap-3 mt-2 flex-wrap">
                    {freelance.average_rating && (
                      <span className="badge badge-primary gap-1">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="currentColor"
                          className="w-3.5 h-3.5"
                        >
                          <path
                            fillRule="evenodd"
                            d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"
                            clipRule="evenodd"
                          />
                        </svg>
                        {freelance.average_rating.toFixed(1)}{" "}
                        {freelance.total_reviews > 0 &&
                          `(${freelance.total_reviews} avis)`}
                      </span>
                    )}
                    {freelance.is_available ? (
                      <span className="badge badge-success gap-1">
                        <span className="w-1.5 h-1.5 rounded-full bg-current animate-pulse" />
                        Disponible
                      </span>
                    ) : (
                      <span className="badge badge-ghost">Indisponible</span>
                    )}
                    <span className="badge badge-outline">
                      {getExperienceLabel(freelance.experience_level)}
                    </span>
                  </div>
                </div>
              </div>

              <div className="divider" />

              <div className="prose max-w-none">
                <p className="whitespace-pre-wrap">
                  {freelance.professional_title || "Aucune bio pour le moment."}
                </p>
              </div>
            </div>
          </div>

          {/* Skills */}
          {freelance.skills && freelance.skills.length > 0 && (
            <div className="card bg-base-100 border border-base-200">
              <div className="card-body p-6">
                <h2 className="font-semibold mb-4">Compétences</h2>
                <div className="flex flex-wrap gap-2">
                  {freelance.skills.map((s) => (
                    <div
                      key={s.skill_id}
                      className="badge badge-lg badge-outline gap-1"
                    >
                      {s.skill.name}
                      {s.proficiency_level && (
                        <span className="text-xs opacity-60">
                          {s.proficiency_level === "beginner"
                            ? "Débutant"
                            : s.proficiency_level === "intermediate"
                              ? "Intermédiaire"
                              : s.proficiency_level === "advanced"
                                ? "Avancé"
                                : "Expert"}
                        </span>
                      )}
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Portfolio */}
          {freelance.portfolio && freelance.portfolio.length > 0 && (
            <div className="card bg-base-100 border border-base-200">
              <div className="card-body p-6">
                <h2 className="font-semibold mb-4">Portfolio</h2>
                <div className="grid sm:grid-cols-2 gap-4">
                  {freelance.portfolio.map((item) => (
                    <div
                      key={item.id}
                      className="border border-base-200 rounded-lg p-4"
                    >
                      <h3 className="font-medium text-sm">{item.title}</h3>
                      {item.description && (
                        <p className="text-xs text-base-content/60 mt-1 line-clamp-2">
                          {item.description}
                        </p>
                      )}
                      {item.project_url && (
                        <a
                          href={item.project_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="link link-primary text-xs mt-2 inline-block"
                        >
                          Voir le projet
                        </a>
                      )}
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="space-y-4">
          <div className="card bg-base-100 border border-base-200">
            <div className="card-body p-5">
              <h3 className="font-semibold mb-4">Tarifs & Stats</h3>

              <div className="space-y-4">
                {freelance.daily_rate_xof && (
                  <div>
                    <span className="text-xs text-base-content/50 uppercase tracking-wider">
                      Taux journalier
                    </span>
                    <p className="text-xl font-bold text-primary">
                      {formatPrice(freelance.daily_rate_xof)}
                      <span className="text-sm font-normal text-base-content/50">
                        /jour
                      </span>
                    </p>
                  </div>
                )}

                <div className="grid grid-cols-2 gap-3 text-center">
                  <div className="bg-base-200 rounded-lg p-3">
                    <div className="font-bold text-lg">
                      {freelance.total_projects_completed}
                    </div>
                    <div className="text-xs text-base-content/50">
                      Missions
                    </div>
                  </div>
                  <div className="bg-base-200 rounded-lg p-3">
                    <div className="font-bold text-lg">
                      {freelance.success_rate
                        ? `${freelance.success_rate}%`
                        : "-"}
                    </div>
                    <div className="text-xs text-base-content/50">
                      Succès
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-3 text-center">
                  <div className="bg-base-200 rounded-lg p-3">
                    <div className="font-bold text-lg">
                      {freelance.response_rate
                        ? `${freelance.response_rate}%`
                        : "-"}
                    </div>
                    <div className="text-xs text-base-content/50">
                      Réponse
                    </div>
                  </div>
                  <div className="bg-base-200 rounded-lg p-3">
                    <div className="font-bold text-lg">
                      {freelance.total_earnings
                        ? formatPrice(freelance.total_earnings)
                        : "-"}
                    </div>
                    <div className="text-xs text-base-content/50">
                      Gains totaux
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <Link
            href={`/auth/login?redirect=/freelancers/${freelance.user_id}`}
            className="btn btn-primary w-full"
          >
            Contacter ce freelance
          </Link>
        </div>
      </div>
    </div>
  );
}
