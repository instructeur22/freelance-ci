"use client";

import { useState, useEffect } from "react";
import { useAuth } from "@/hooks/useAuth";
import { useAuthStore } from "@/stores/auth";
import { authApi } from "@/lib/api/endpoints/auth";
import { supabase } from "@/lib/supabase/client";
import { useRouter } from "next/navigation";
import Link from "next/link";

function getCookie(name: string): string | null {
  if (typeof document === "undefined") return null;
  for (const cookie of document.cookie.split(";")) {
    const idx = cookie.indexOf("=");
    if (idx === -1) continue;
    if (cookie.slice(0, idx).trim() === name) return cookie.slice(idx + 1).trim();
  }
  return null;
}

export default function OnboardingPage() {
  const { user, isLoading, isAuthenticated } = useAuth();
  const router = useRouter();
  const [error, setError] = useState("");
  const [saving, setSaving] = useState(false);
  const [checking, setChecking] = useState(true);

  useEffect(() => {
    if (isLoading) return;

    if (isAuthenticated && user) {
      if (user.last_login_at) {
        router.push("/dashboard");
        return;
      }
      setChecking(false);
      return;
    }

    const fcToken = getCookie("fc_at");
    if (!fcToken) {
      router.push("/auth/login");
      return;
    }

    (async () => {
      try {
        const { data: { session } } = await supabase.auth.getSession();
        const token = session?.access_token || fcToken;

        const res = await fetch(
          `${process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api"}/auth/sync`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
          }
        );

        if (!res.ok) {
          throw new Error(await res.text().catch(() => "sync failed"));
        }

        const meRes = await fetch(
          `${process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api"}/auth/me`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
          }
        );

        if (meRes.ok) {
          const userData = (await meRes.json()).data;
          if (userData.last_login_at) {
            router.push("/dashboard");
            return;
          }
        }

        setChecking(false);
      } catch (e) {
        console.error("Onboarding: failed to sync user", e);
        router.push("/auth/login");
      }
    })();
  }, [user, isLoading, isAuthenticated, router]);

  const handleRoleSelect = async (role: "client" | "freelance") => {
    setSaving(true);
    setError("");

    try {
      const res = await authApi.onboarding(role);
      useAuthStore.getState().setUser(res.data);
      router.push("/dashboard");
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur lors de l'enregistrement");
      setSaving(false);
    }
  };

  if (isLoading || checking) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-base-200">
        <span className="loading loading-spinner loading-lg text-primary" />
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center px-4 py-12 bg-base-200">
      <div className="w-full max-w-2xl">
        <div className="text-center mb-8">
          <Link href="/" className="inline-block">
            <img src="/Freelance-CI.jpeg" alt="Freelance CI" width={52} height={52} className="mx-auto rounded-lg" />
          </Link>
          <h1 className="text-2xl font-bold mt-4">Bienvenue sur Freelance CI</h1>
          <p className="text-base-content/60 mt-1">Choisissez comment vous souhaitez utiliser la plateforme</p>
        </div>

        {error && (
          <div className="alert alert-error text-sm mb-6 max-w-md mx-auto">{error}</div>
        )}

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <button
            onClick={() => handleRoleSelect("client")}
            disabled={saving}
            className="card bg-base-100 border-2 border-base-200 hover:border-primary hover:shadow-lg transition-all p-8 text-left group disabled:opacity-50"
          >
            <div className="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary/20 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" className="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h2 className="text-xl font-semibold mb-2">Je suis un Client</h2>
            <p className="text-base-content/60 text-sm leading-relaxed">
              Je publie des projets et je recrute des freelances pour les réaliser.
            </p>
            <ul className="mt-4 space-y-2 text-sm text-base-content/50">
              <li className="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg> Publier des projets</li>
              <li className="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg> Recevoir des devis</li>
              <li className="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg> Paiement sécurisé</li>
            </ul>
          </button>

          <button
            onClick={() => handleRoleSelect("freelance")}
            disabled={saving}
            className="card bg-base-100 border-2 border-base-200 hover:border-secondary hover:shadow-lg transition-all p-8 text-left group disabled:opacity-50"
          >
            <div className="w-14 h-14 rounded-2xl bg-secondary/10 flex items-center justify-center mb-4 group-hover:bg-secondary/20 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" className="w-7 h-7 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h2 className="text-xl font-semibold mb-2">Je suis un Freelance</h2>
            <p className="text-base-content/60 text-sm leading-relaxed">
              Je propose mes services et je réponds aux projets qui m'intéressent.
            </p>
            <ul className="mt-4 space-y-2 text-sm text-base-content/50">
              <li className="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg> Trouver des missions</li>
              <li className="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg> Envoyer des devis</li>
              <li className="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 text-success shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg> Recevoir des paiements</li>
            </ul>
          </button>
        </div>

        <p className="text-center text-xs text-base-content/40 mt-8">
          Vous pourrez modifier ces informations plus tard dans votre profil
        </p>
      </div>
    </div>
  );
}
