import { api } from "@/lib/api/client";
import type { Notification } from "@/types/api";

export const notificationApi = {
  list: () => api.list<Notification>("/notifications"),

  markRead: (id: string) => api.put(`/notifications/${id}/read`),

  markAllRead: () => api.put("/notifications/read-all"),

  delete: (id: string) => api.delete(`/notifications/${id}`),
};
