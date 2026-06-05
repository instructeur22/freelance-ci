import { api } from "@/lib/api/client";
import type {
  JobCategory,
  Skill,
  Project,
  FreelanceProfile,
} from "@/types/api";

export const publicApi = {
  // Catégories
  categories: () => api.list<JobCategory>("/categories"),
  categorySkills: (id: string) =>
    api.list<Skill>(`/categories/${id}/skills`),

  // Freelances
  freelances: (params?: Record<string, string>) =>
    api.list<FreelanceProfile>("/freelances", params),
  freelanceDetail: (id: string) =>
    api.get<FreelanceProfile>(`/freelances/${id}`),

  // Projets
  projects: (params?: Record<string, string>) =>
    api.list<Project>("/projects", params),
  projectDetail: (id: string) => api.get<Project>(`/projects/${id}`),

  // Plans d'abonnement
  subscriptionPlans: () => api.list<any>("/subscriptions/plans"),
};
