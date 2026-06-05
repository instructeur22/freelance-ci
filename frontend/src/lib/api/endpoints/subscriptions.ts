import { api } from "@/lib/api/client";
import type { SubscriptionPlan, FreelanceSubscription } from "@/types/api";

export const subscriptionApi = {
  plans: () => api.list<SubscriptionPlan>("/subscriptions/plans"),

  purchase: (data: { plan_id: string; billing_cycle: "monthly" | "yearly" }) =>
    api.post<{ payment_url: string }>("/subscriptions/purchase", data),

  current: () => api.get<FreelanceSubscription>("/subscriptions"),

  cancel: () => api.post("/subscriptions/cancel"),

  upgrade: (data: { plan_id: string }) =>
    api.post("/subscriptions/upgrade", data),
};
