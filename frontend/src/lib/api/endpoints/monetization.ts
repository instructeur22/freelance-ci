import { api } from "@/lib/api/client";
import type { VerifiedBadge, Boost } from "@/types/api";

export const badgeApi = {
  purchase: (data: { badge_type: string }) =>
    api.post<{ payment_url: string }>("/badges/purchase", data),

  status: () => api.get<VerifiedBadge>("/badges"),
};

export const boostApi = {
  purchase: (data: { target: "profile" | "project"; target_id?: string; duration: "7_days" | "30_days" }) =>
    api.post<{ payment_url: string }>("/boosts/purchase", data),

  list: () => api.list<Boost>("/boosts"),
};
