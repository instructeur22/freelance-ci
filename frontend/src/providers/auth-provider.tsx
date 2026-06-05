"use client";

import { useEffect } from "react";
import { supabase } from "@/lib/supabase/client";
import { authApi } from "@/lib/api/endpoints/auth";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "next/navigation";

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const { setUser, setLoading } = useAuthStore();
  const router = useRouter();

  useEffect(() => {
    const init = async () => {
      try {
        const res = await authApi.me();
        setUser(res.data);
        return;
      } catch {
        setUser(null);
      }
    };

    init();

    const { data: { subscription } } = supabase.auth.onAuthStateChange(
      async (event, session) => {
        if (event === "SIGNED_IN" && session?.access_token) {
          try {
            const res = await authApi.me();
            setUser(res.data);
            router.refresh();
          } catch {
            setUser(null);
          }
        } else if (event === "SIGNED_OUT") {
          setUser(null);
          router.refresh();
        }
      }
    );

    return () => subscription.unsubscribe();
  }, [setUser, setLoading, router]);

  return <>{children}</>;
}
