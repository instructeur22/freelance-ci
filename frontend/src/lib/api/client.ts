import type { ApiResponse, PaginatedResponse } from "@/types/api";
import { supabase } from "@/lib/supabase/client";

const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

async function getToken(): Promise<string | null> {
  const { data } = await supabase.auth.getSession();
  if (data.session?.access_token) return data.session.access_token;

  if (typeof document !== "undefined") {
    for (const cookie of document.cookie.split(";")) {
      const idx = cookie.indexOf("=");
      if (idx === -1) continue;
      if (cookie.slice(0, idx).trim() === "fc_at") return cookie.slice(idx + 1).trim();
    }
  }
  return null;
}

async function request<T>(
  endpoint: string,
  options: RequestInit = {},
  isFormData = false
): Promise<T> {
  const token = await getToken();

  const headers: HeadersInit = {
    Accept: "application/json",
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
    ...(!isFormData ? { "Content-Type": "application/json" } : {}),
    ...options.headers,
  };

  const res = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers,
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({ message: res.statusText }));
    throw new Error(error.message || `Erreur ${res.status}`);
  }

  // Handle 204 No Content
  if (res.status === 204) return undefined as T;

  return res.json();
}

function buildUrl(endpoint: string, params?: Record<string, string>) {
  if (!params) return endpoint;
  const qs = new URLSearchParams(params).toString();
  return qs ? `${endpoint}?${qs}` : endpoint;
}

export const api = {
  get: <T>(endpoint: string, params?: Record<string, string>) =>
    request<ApiResponse<T>>(buildUrl(endpoint, params)),

  list: <T>(endpoint: string, params?: Record<string, string>) =>
    request<PaginatedResponse<T>>(buildUrl(endpoint, params)),

  post: <T>(endpoint: string, data?: unknown) =>
    request<ApiResponse<T>>(endpoint, {
      method: "POST",
      body: data ? JSON.stringify(data) : undefined,
    }),

  put: <T>(endpoint: string, data?: unknown) =>
    request<ApiResponse<T>>(endpoint, {
      method: "PUT",
      body: data ? JSON.stringify(data) : undefined,
    }),

  delete: <T>(endpoint: string) =>
    request<ApiResponse<T>>(endpoint, { method: "DELETE" }),

  upload: <T>(endpoint: string, formData: FormData) =>
    request<ApiResponse<T>>(endpoint, {
      method: "POST",
      body: formData,
    }, true),
};
