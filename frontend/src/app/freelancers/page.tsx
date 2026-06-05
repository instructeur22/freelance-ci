"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/lib/api/endpoints/public";
import { FreelancerCard } from "@/components/shared/FreelancerCard";
import Link from "next/link";

export default function FreelancersPage() {
  const [search, setSearch] = useState("");
  const [category, setCategory] = useState("");
  const [minRate, setMinRate] = useState("");
  const [maxRate, setMaxRate] = useState("");
  const [ratingMin, setRatingMin] = useState("");
  const [sortBy, setSortBy] = useState("created_at");
  const [page, setPage] = useState(1);

  const params: Record<string, string> = {};
  if (search) params.search = search;
  if (category) params.category = category;
  if (minRate) params.min_rate = minRate;
  if (maxRate) params.max_rate = maxRate;
  if (ratingMin) params.rating_min = ratingMin;
  params.sort_by = sortBy;
  params.sort_order = "desc";
  params.page = String(page);

  const { data, isLoading } = useQuery({
    queryKey: ["freelancers", params],
    queryFn: () => publicApi.freelances(params),
  });

  const freelancers = data?.data ?? [];
  const meta = data?.meta;

  return (
    <div className="container mx-auto px-4 py-12">
      <div className="mb-8">
        <h1 className="text-3xl font-bold">Freelances</h1>
        <p className="text-base-content/70 mt-2">
          Trouvez le freelance idéal pour votre projet
        </p>
      </div>

      {/* Filters */}
      <div className="card bg-base-200/50 border border-base-200 mb-8">
        <div className="card-body p-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input
              type="text"
              placeholder="Rechercher..."
              className="input input-bordered input-sm"
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            />
            <input
              type="text"
              placeholder="Catégorie/métier"
              className="input input-bordered input-sm"
              value={category}
              onChange={(e) => { setCategory(e.target.value); setPage(1); }}
            />
            <input
              type="number"
              placeholder="Tarif min (XOF)"
              className="input input-bordered input-sm"
              value={minRate}
              onChange={(e) => { setMinRate(e.target.value); setPage(1); }}
            />
            <input
              type="number"
              placeholder="Tarif max (XOF)"
              className="input input-bordered input-sm"
              value={maxRate}
              onChange={(e) => { setMaxRate(e.target.value); setPage(1); }}
            />
            <select
              className="select select-bordered select-sm"
              value={sortBy}
              onChange={(e) => { setSortBy(e.target.value); setPage(1); }}
            >
              <option value="created_at">Plus récents</option>
              <option value="average_rating">Meilleure note</option>
            </select>
          </div>
        </div>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center min-h-[40vh]">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : freelancers.length === 0 ? (
        <div className="text-center py-20 text-base-content/50">
          <p>Aucun freelance trouvé.</p>
          <Link href="/projects" className="btn btn-ghost mt-4">
            Voir les projets
          </Link>
        </div>
      ) : (
        <>
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            {freelancers.map((freelance) => (
              <FreelancerCard key={freelance.id} freelance={freelance} />
            ))}
          </div>

          {meta && meta.last_page > 1 && (
            <div className="flex justify-center gap-2 mt-8">
              <button
                className="btn btn-sm"
                disabled={page <= 1}
                onClick={() => setPage((p) => Math.max(1, p - 1))}
              >
                «
              </button>
              <span className="btn btn-sm btn-ghost">
                {meta.current_page} / {meta.last_page}
              </span>
              <button
                className="btn btn-sm"
                disabled={page >= (meta.last_page ?? 1)}
                onClick={() => setPage((p) => p + 1)}
              >
                »
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
