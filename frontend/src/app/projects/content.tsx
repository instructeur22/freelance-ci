"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/lib/api/endpoints/public";
import { ProjectCard } from "@/components/shared/ProjectCard";
import { useSearchParams, useRouter } from "next/navigation";

export default function ProjectsContent() {
  const searchParams = useSearchParams();
  const router = useRouter();

  const categoryParam = searchParams.get("category");
  const searchParam = searchParams.get("search");
  const pageParam = searchParams.get("page");

  const [search, setSearch] = useState(searchParam || "");
  const [budgetMin, setBudgetMin] = useState("");
  const [budgetMax, setBudgetMax] = useState("");
  const [isRemote, setIsRemote] = useState("");

  const params: Record<string, string> = {};
  if (categoryParam) params.category_id = categoryParam;
  if (searchParam) params.search = searchParam;
  if (budgetMin) params.budget_min = budgetMin;
  if (budgetMax) params.budget_max = budgetMax;
  if (isRemote) params.is_remote = isRemote;
  if (pageParam) params.page = pageParam;

  const { data, isLoading } = useQuery({
    queryKey: ["projects", params],
    queryFn: () => publicApi.projects(params),
  });

  const projects = data?.data ?? [];
  const meta = data?.meta;

  const applyFilters = () => {
    const sp = new URLSearchParams();
    if (search) sp.set("search", search);
    if (budgetMin) sp.set("budget_min", budgetMin);
    if (budgetMax) sp.set("budget_max", budgetMax);
    if (isRemote) sp.set("is_remote", isRemote);
    if (categoryParam) sp.set("category", categoryParam);
    router.push(`/projects?${sp.toString()}`);
  };

  const goToPage = (page: number) => {
    const sp = new URLSearchParams(searchParams.toString());
    sp.set("page", String(page));
    router.push(`/projects?${sp.toString()}`);
  };

  return (
    <div className="container mx-auto px-4 py-12">
      <div className="mb-8">
        <h1 className="text-3xl font-bold">
          {categoryParam
            ? `Projets - ${categoryParam}`
            : "Projets disponibles"}
        </h1>
        <p className="text-base-content/70 mt-2">
          Trouvez le projet qui vous correspond
        </p>
      </div>

      {/* Filters */}
      <div className="card bg-base-200/50 border border-base-200 mb-8">
        <div className="card-body p-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input
              type="text"
              placeholder="Rechercher un projet..."
              className="input input-bordered input-sm"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              onKeyDown={(e) => e.key === "Enter" && applyFilters()}
            />
            <input
              type="number"
              placeholder="Budget min"
              className="input input-bordered input-sm"
              value={budgetMin}
              onChange={(e) => setBudgetMin(e.target.value)}
            />
            <input
              type="number"
              placeholder="Budget max"
              className="input input-bordered input-sm"
              value={budgetMax}
              onChange={(e) => setBudgetMax(e.target.value)}
            />
            <select
              className="select select-bordered select-sm"
              value={isRemote}
              onChange={(e) => setIsRemote(e.target.value)}
            >
              <option value="">Tous les types</option>
              <option value="1">Télétravail</option>
              <option value="0">Sur site</option>
            </select>
            <button onClick={applyFilters} className="btn btn-primary btn-sm">
              Filtrer
            </button>
          </div>
        </div>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center min-h-[40vh]">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : projects.length === 0 ? (
        <div className="text-center py-20 text-base-content/50">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-16 w-16 mx-auto mb-4 opacity-30"
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
          <p>Aucun projet trouvé.</p>
          {categoryParam && (
            <button
              onClick={() => router.push("/projects")}
              className="btn btn-ghost btn-sm mt-2"
            >
              Voir tous les projets
            </button>
          )}
        </div>
      ) : (
        <>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            {projects.map((project) => (
              <ProjectCard key={project.id} project={project} />
            ))}
          </div>

          {meta && meta.last_page > 1 && (
            <div className="flex justify-center mt-8 gap-2">
              {Array.from({ length: meta.last_page }, (_, i) => i + 1).map(
                (page) => (
                  <button
                    key={page}
                    onClick={() => goToPage(page)}
                    className={`btn btn-sm ${
                      page === (meta?.current_page ?? 1)
                        ? "btn-primary"
                        : "btn-ghost"
                    }`}
                  >
                    {page}
                  </button>
                )
              )}
            </div>
          )}
        </>
      )}
    </div>
  );
}
