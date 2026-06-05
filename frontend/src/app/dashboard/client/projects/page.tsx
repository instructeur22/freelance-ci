"use client";

import { useQuery } from "@tanstack/react-query";
import { projectApi } from "@/lib/api/endpoints/projects";
import { ProjectCard } from "@/components/shared/ProjectCard";
import Link from "next/link";

export default function ClientProjectsPage() {
  const { data, isLoading } = useQuery({
    queryKey: ["my-projects"],
    queryFn: () => projectApi.list(),
  });

  const projects = data?.data ?? [];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Mes projets</h1>
          <p className="text-base-content/70 text-sm mt-1">
            Gérez vos projets publiés
          </p>
        </div>
        <Link href="/dashboard/client/projects/new" className="btn btn-primary">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="w-5 h-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M12 4v16m8-8H4"
            />
          </svg>
          Nouveau projet
        </Link>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : projects.length === 0 ? (
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
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
              />
            </svg>
            <h3 className="font-semibold mb-1">Aucun projet</h3>
            <p className="text-sm text-base-content/60 mb-4">
              Publiez votre premier projet pour trouver des freelances
            </p>
            <Link href="/dashboard/client/projects/new" className="btn btn-primary">
              Publier un projet
            </Link>
          </div>
        </div>
      ) : (
        <div className="grid md:grid-cols-2 gap-4">
          {projects.map((project) => (
            <ProjectCard key={project.id} project={project} />
          ))}
        </div>
      )}
    </div>
  );
}
