import Link from "next/link";
import type { FreelanceProfile } from "@/types/api";
import { formatPrice, getExperienceLabel } from "@/lib/utils/format";

export function FreelancerCard({
  freelance,
}: {
  freelance: FreelanceProfile;
}) {
  return (
    <Link href={`/freelancers/${freelance.user_id}`}>
      <div className="card bg-base-100 border border-base-200 hover:border-primary/30 hover:shadow-md transition-all duration-200 h-full">
        <div className="card-body p-5">
          <div className="flex items-center gap-3">
            <div className="avatar placeholder">
              <div className="w-12 h-12 rounded-full bg-primary text-primary-content">
                <span className="text-sm font-medium">
                  {freelance.professional_title?.charAt(0) || "F"}
                </span>
              </div>
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="font-semibold text-sm truncate">
                {freelance.professional_title || "Freelance"}
              </h3>
              {freelance.tagline && (
                <p className="text-xs text-base-content/60 truncate">
                  {freelance.tagline}
                </p>
              )}
            </div>
            {freelance.average_rating && (
              <div className="badge badge-soft badge-primary gap-1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="currentColor"
                  className="w-3 h-3"
                >
                  <path
                    fillRule="evenodd"
                    d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"
                    clipRule="evenodd"
                  />
                </svg>
                {freelance.average_rating.toFixed(1)}
              </div>
            )}
          </div>

          {/* Skills */}
          {freelance.skills && freelance.skills.length > 0 && (
            <div className="flex flex-wrap gap-1 mt-3">
              {freelance.skills.slice(0, 4).map((s) => (
                <span key={s.skill_id} className="badge badge-ghost badge-xs">
                  {s.skill.name}
                </span>
              ))}
              {freelance.skills.length > 4 && (
                <span className="badge badge-ghost badge-xs">
                  +{freelance.skills.length - 4}
                </span>
              )}
            </div>
          )}

          <div className="flex items-center justify-between mt-3 pt-3 border-t border-base-200">
            <div className="text-sm font-medium text-primary">
              {freelance.daily_rate_xof
                ? `${formatPrice(freelance.daily_rate_xof)}/jour`
                : "Tarif non défini"}
            </div>
            <div className="flex items-center gap-3 text-xs text-base-content/50">
              <span>
                {getExperienceLabel(freelance.experience_level)}
              </span>
              <span>{freelance.total_projects_completed} missions</span>
            </div>
          </div>
        </div>
      </div>
    </Link>
  );
}
