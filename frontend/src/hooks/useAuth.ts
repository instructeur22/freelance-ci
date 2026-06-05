"use client";

import { useAuthStore } from "@/stores/auth";
import { supabase } from "@/lib/supabase/client";
import { useRouter } from "next/navigation";
import { useCallback } from "react";

export function useAuth() {
  const { user, isLoading, isAuthenticated, logout: storeLogout } =
    useAuthStore();
  const router = useRouter();

  const login = useCallback(
    async (email: string, password: string) => {
      const { error } = await supabase.auth.signInWithPassword({
        email,
        password,
      });
      if (error) throw error;
      router.push("/dashboard");
    },
    [router]
  );

  const loginWithSocial = useCallback(
    async (provider: "google" | "github" | "linkedin") => {
      const { error } = await supabase.auth.signInWithOAuth({
        provider,
        options: {
          redirectTo: `${window.location.origin}/auth/callback`,
        },
      });
      if (error) throw error;
    },
    []
  );

  const register = useCallback(
    async (email: string, password: string) => {
      const { error } = await supabase.auth.signUp({
        email,
        password,
        options: {
          emailRedirectTo: `${window.location.origin}/auth/callback`,
        },
      });
      if (error) throw error;
    },
    []
  );

  const logout = useCallback(async () => {
    await supabase.auth.signOut();
    storeLogout();
    router.push("/");
  }, [storeLogout, router]);

  return {
    user,
    isLoading,
    isAuthenticated,
    login,
    loginWithSocial,
    register,
    logout,
  };
}
