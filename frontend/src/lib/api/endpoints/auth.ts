import { api } from "@/lib/api/client";
import type { User } from "@/types/api";

export const authApi = {
  me: () => api.get<User>("/auth/me"),

  login: (token: string) =>
    api.post<User>("/auth/login", { token }),

  register: (data: {
    email: string;
    password: string;
    first_name: string;
    last_name: string;
    role: "client" | "freelance";
    phone?: string;
    referral_code?: string;
  }) => api.post<User>("/auth/register", data),

  socialAuth: (provider: string, token: string) =>
    api.post<User>(`/auth/social/${provider}`, { token }),

  sync: () => api.post<User>("/auth/sync"),

  onboarding: (role: "client" | "freelance") =>
    api.post<User>("/auth/onboarding", { role }),
};
