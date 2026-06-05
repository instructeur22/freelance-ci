import { api } from "@/lib/api/client";
import type { Review } from "@/types/api";

export const reviewApi = {
  create: (contractId: string, data: { rating: number; comment?: string; rating_quality?: number; rating_delay?: number; rating_communication?: number }) =>
    api.post<Review>(`/contracts/${contractId}/review`, data),

  listForFreelance: (freelanceId: string) =>
    api.list<Review>(`/freelances/${freelanceId}/reviews`),

  reply: (reviewId: string, data: { comment: string }) =>
    api.post(`/reviews/${reviewId}/reply`, data),
};
