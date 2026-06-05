import { api } from "@/lib/api/client";
import type { Quote, Contract } from "@/types/api";

export const quoteApi = {
  listForProject: (projectId: string) =>
    api.list<Quote>(`/projects/${projectId}/quotes`),

  submit: (projectId: string, data: Partial<Quote>) =>
    api.post<Quote>(`/projects/${projectId}/quotes`, data),

  detail: (id: string) => api.get<Quote>(`/quotes/${id}`),

  update: (id: string, data: Partial<Quote>) =>
    api.put<Quote>(`/quotes/${id}`, data),

  withdraw: (id: string) => api.delete(`/quotes/${id}`),

  accept: (id: string) =>
    api.post<{ contract: Contract }>(`/quotes/${id}/accept`),

  refuse: (id: string) => api.post(`/quotes/${id}/refuse`),
};
