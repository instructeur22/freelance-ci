"use client";

import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/lib/api/endpoints/public";
import { CategoryCard } from "@/components/shared/CategoryCard";

export default function CategoriesPage() {
  const { data, isLoading } = useQuery({
    queryKey: ["categories"],
    queryFn: () => publicApi.categories(),
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

  const categories = data?.data ?? [];

  return (
    <div className="container mx-auto px-4 py-12">
      <div className="mb-8">
        <h1 className="text-3xl font-bold">Catégories</h1>
        <p className="text-base-content/70 mt-2">
          Explorez les projets par catégorie
        </p>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        {categories.map((category) => (
          <CategoryCard key={category.id} category={category} />
        ))}
      </div>

      {categories.length === 0 && (
        <div className="text-center py-20 text-base-content/50">
          <p>Aucune catégorie pour le moment.</p>
        </div>
      )}
    </div>
  );
}
