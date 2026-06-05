import { api } from "@/lib/api/client";
import type { Contract, Milestone } from "@/types/api";

export const contractApi = {
  list: () => api.list<Contract>("/contracts"),

  detail: (id: string) => api.get<Contract>(`/contracts/${id}`),

  sign: (id: string) => api.post(`/contracts/${id}/sign`),

  addMilestone: (contractId: string, data: Partial<Milestone>) =>
    api.post<Milestone>(`/contracts/${contractId}/milestones`, data),

  updateMilestone: (contractId: string, milestoneId: string, data: Partial<Milestone>) =>
    api.put<Milestone>(`/contracts/${contractId}/milestones/${milestoneId}`, data),

  deliverMilestone: (contractId: string, milestoneId: string) =>
    api.post(`/contracts/${contractId}/milestones/${milestoneId}/deliver`),

  validateMilestone: (contractId: string, milestoneId: string) =>
    api.post(`/contracts/${contractId}/milestones/${milestoneId}/validate`),
};
