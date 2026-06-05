import { api } from "@/lib/api/client";
import type { User, Report, Dispute, Payment, Verification, PlatformSetting, VerifiedBadge } from "@/types/api";

export const adminApi = {
  dashboard: () =>
    api.get<{
      total_users: number;
      total_freelances: number;
      total_clients: number;
      total_projects: number;
      total_contracts: number;
      total_revenue: number;
      pending_verifications: number;
      open_disputes: number;
    }>("/admin/dashboard"),

  users: () => api.list<User>("/admin/users"),

  updateUserStatus: (id: string, status: string) =>
    api.put(`/admin/users/${id}/status`, { status }),

  verifications: () => api.list<Verification>("/admin/verifications"),

  approveVerification: (id: string) =>
    api.post(`/admin/verifications/${id}/approve`),

  rejectVerification: (id: string, notes?: string) =>
    api.post(`/admin/verifications/${id}/reject`, { notes }),

  reports: () => api.list<Report>("/admin/reports"),

  resolveReport: (id: string, notes?: string) =>
    api.put(`/admin/reports/${id}`, { status: "resolved", admin_notes: notes }),

  disputes: () => api.list<Dispute>("/admin/disputes"),

  resolveDispute: (id: string, data: { status: string; admin_notes?: string }) =>
    api.put(`/admin/disputes/${id}`, data),

  payments: () => api.list<Payment>("/admin/payments"),

  settings: () => api.get<PlatformSetting[]>("/admin/settings"),

  updateSetting: (key: string, value: string) =>
    api.put(`/admin/settings/${key}`, { value }),

  badges: () => api.list<VerifiedBadge>("/admin/badges"),

  grantBadge: (data: { freelance_id: string; badge_type: string }) =>
    api.post("/admin/badges/grant", data),

  revokeBadge: (id: string) =>
    api.post(`/admin/badges/${id}/revoke`),

  boosts: () => api.list<any>("/admin/boosts"),

  revokeBoost: (id: string) =>
    api.post(`/admin/boosts/${id}/revoke`),
};
