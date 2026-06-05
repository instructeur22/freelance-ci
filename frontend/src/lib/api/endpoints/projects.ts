import { api } from "@/lib/api/client";
import type { Project } from "@/types/api";

export const projectApi = {
  list: (params?: Record<string, string>) =>
    api.list<Project>("/projects", params),

  detail: (id: string) => api.get<Project>(`/projects/${id}`),

  create: (data: Partial<Project>) =>
    api.post<Project>("/projects", data),

  update: (id: string, data: Partial<Project>) =>
    api.put<Project>(`/projects/${id}`, data),

  delete: (id: string) => api.delete(`/projects/${id}`),

  addFile: (projectId: string, formData: FormData) =>
    api.upload(`/projects/${projectId}/files`, formData),

  removeFile: (projectId: string, fileId: string) =>
    api.delete(`/projects/${projectId}/files/${fileId}`),
};
