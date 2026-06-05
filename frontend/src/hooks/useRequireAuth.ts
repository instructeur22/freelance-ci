"use client";

import { useAuthStore } from "@/stores/auth";
import { useRouter } from "next/navigation";
import { useEffect } from "react";

export function useRequireAuth(redirectTo = "/auth/login") {
  const { isAuthenticated, isLoading } = useAuthStore();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push(redirectTo);
    }
  }, [isAuthenticated, isLoading, router, redirectTo]);

  return { isAuthenticated, isLoading };
}

export function useRequireRole(
  role: "client" | "freelance" | "admin",
  redirectTo = "/"
) {
  const { user, isLoading, isAuthenticated } = useAuthStore();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push("/auth/login");
      return;
    }
    if (!isLoading && user && user.role !== role) {
      router.push(redirectTo);
    }
  }, [user, isLoading, isAuthenticated, router, role, redirectTo]);
}
