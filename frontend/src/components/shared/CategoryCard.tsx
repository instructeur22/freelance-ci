import Link from "next/link";
import type { JobCategory } from "@/types/api";

export function CategoryCard({ category }: { category: JobCategory }) {
  return (
    <Link href={`/projects?category=${category.slug}`}>
      <div className="card bg-base-100 border border-base-200 hover:border-primary/30 hover:shadow-md transition-all duration-200">
        <div className="card-body items-center text-center p-5">
          {category.icon_url ? (
            <img
              src={category.icon_url}
              alt={category.name}
              className="w-10 h-10 object-contain"
            />
          ) : (
            <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="w-5 h-5 text-primary"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                />
              </svg>
            </div>
          )}
          <h3 className="font-semibold text-sm">{category.name}</h3>
          {category.description && (
            <p className="text-xs text-base-content/60 line-clamp-2">
              {category.description}
            </p>
          )}
          <span className="badge badge-ghost badge-xs mt-1">
            Voir les projets
          </span>
        </div>
      </div>
    </Link>
  );
}
