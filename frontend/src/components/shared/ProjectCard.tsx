import Link from "next/link";
import type { Project } from "@/types/api";
import { formatPrice, getProjectStatusLabel } from "@/lib/utils/format";

export function ProjectCard({ project }: { project: Project }) {
  return (
    <Link href={`/projects/${project.id}`}>
      <div className="card bg-base-100 border border-base-200 hover:border-primary/30 hover:shadow-md transition-all duration-200 h-full">
        <div className="card-body p-5">
          <div className="flex items-start justify-between gap-2">
            <div className="flex-1 min-w-0">
              <h3 className="card-title text-base font-semibold line-clamp-1">
                {project.title}
              </h3>
              {project.is_urgent && (
                <span className="badge badge-error badge-xs mt-1">Urgent</span>
              )}
            </div>
            <span className="badge badge-ghost badge-sm whitespace-nowrap">
              {getProjectStatusLabel(project.status)}
            </span>
          </div>

          <p className="text-sm text-base-content/70 line-clamp-2 mt-1">
            {project.description}
          </p>

          <div className="flex items-center gap-2 mt-2 flex-wrap">
            {project.category && (
              <span className="badge badge-outline badge-sm">
                {project.category.name}
              </span>
            )}
            {project.is_remote && (
              <span className="badge badge-outline badge-sm">À distance</span>
            )}
            {project.location && (
              <span className="badge badge-outline badge-sm">
                {project.location}
              </span>
            )}
          </div>

          <div className="flex items-center justify-between mt-3 pt-3 border-t border-base-200">
            <div className="text-sm font-medium text-primary">
              {project.budget_min && project.budget_max
                ? `${formatPrice(project.budget_min)} - ${formatPrice(project.budget_max)}`
                : "Budget non spécifié"}
            </div>
            <div className="flex items-center gap-3 text-xs text-base-content/50">
              <span>{project.quotes_count} devis</span>
              <span>{project.views_count} vues</span>
            </div>
          </div>
        </div>
      </div>
    </Link>
  );
}
