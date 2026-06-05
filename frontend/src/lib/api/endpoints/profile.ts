import { api } from "@/lib/api/client";
import type {
  Profile,
  ClientProfile,
  FreelanceProfile,
  User,
} from "@/types/api";

export const profileApi = {
  me: () => api.get<Profile & { user: User }>("/profiles/me"),
  updateMe: (data: Partial<Profile>) =>
    api.put<Profile>("/profiles/me", data),

  client: () => api.get<ClientProfile>("/profiles/client"),
  updateClient: (data: Partial<ClientProfile>) =>
    api.put<ClientProfile>("/profiles/client", data),

  freelance: () => api.get<FreelanceProfile>("/profiles/freelance"),
  updateFreelance: (data: Partial<FreelanceProfile>) =>
    api.put<FreelanceProfile>("/profiles/freelance", data),

  addSkill: (skillId: string, level: string) =>
    api.post("/profiles/freelance/skills", { skill_id: skillId, proficiency_level: level }),
  removeSkill: (skillId: string) =>
    api.delete(`/profiles/freelance/skills/${skillId}`),

  addPortfolio: (data: FormData) =>
    api.post("/profiles/freelance/portfolio", data),
  removePortfolio: (id: string) =>
    api.delete(`/profiles/freelance/portfolio/${id}`),
};
