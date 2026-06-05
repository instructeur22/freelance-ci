import { api } from "@/lib/api/client";
import type { Payment } from "@/types/api";

export const paymentApi = {
  list: () => api.list<Payment>("/payments"),

  detail: (id: string) => api.get<Payment>(`/payments/${id}`),

  initiate: (data: { contract_id: string; payment_channel: string; customer_phone?: string; customer_email?: string }) =>
    api.post<{ payment_url: string; transaction_id: string }>("/payments/initiate", data),

  confirm: (id: string) => api.post(`/payments/${id}/confirm`),
};
