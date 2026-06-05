"use client";

import { useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import Link from "next/link";
import { supabase } from "@/lib/supabase/client";
import { authApi } from "@/lib/api/endpoints/auth";

export default function RegisterForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const defaultRole = searchParams.get("role") || "";

  const [step, setStep] = useState(1);
  const [role, setRole] = useState<"client" | "freelance" | "">(defaultRole as "client" | "freelance" | "");
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setIsLoading(true);

    try {
      const { error: authError } = await supabase.auth.signUp({
        email,
        password,
        options: { emailRedirectTo: `${window.location.origin}/auth/callback` },
      });

      if (authError) throw authError;

      await authApi.register({
        email,
        password,
        first_name: firstName,
        last_name: lastName,
        role: role as "client" | "freelance",
        phone: phone || undefined,
      });

      router.push("/auth/login?registered=true");
    } catch (err) {
      setError(err instanceof Error ? err.message : "Erreur lors de l'inscription");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-[80vh] flex items-center justify-center px-4 py-12 bg-base-200">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <Link href="/" className="inline-block">
            <img src="/Freelance-CI.jpeg" alt="Freelance CI" width={52} height={52} className="mx-auto mb-4 rounded-lg" />
          </Link>
          <h1 className="text-2xl font-bold">Inscription</h1>
          <p className="text-base-content/60 mt-1">Rejoignez la communauté Freelance CI</p>
        </div>

        <div className="card bg-base-100 border border-base-200 shadow-sm">
          <div className="card-body p-6">
            {error && <div className="alert alert-error text-sm mb-4">{error}</div>}

            {step === 1 && !role && (
              <form className="space-y-4">
                <p className="text-sm text-base-content/60 mb-4">Je souhaite m&apos;inscrire en tant que :</p>
                <div className="grid grid-cols-2 gap-3">
                  <button type="button" onClick={() => { setRole("freelance"); setStep(2); }}
                    className="card border-2 p-5 text-center hover:border-primary hover:bg-primary/5 transition-all cursor-pointer"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8 mx-auto mb-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span className="font-semibold text-sm">Freelance</span>
                    <p className="text-xs text-base-content/50 mt-1">Je propose mes services</p>
                  </button>
                  <button type="button" onClick={() => { setRole("client"); setStep(2); }}
                    className="card border-2 p-5 text-center hover:border-primary hover:bg-primary/5 transition-all cursor-pointer"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8 mx-auto mb-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span className="font-semibold text-sm">Client</span>
                    <p className="text-xs text-base-content/50 mt-1">Je cherche des talents</p>
                  </button>
                </div>
              </form>
            )}

            {step === 2 && role && (
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="flex items-center gap-3 mb-4">
                  <button type="button" onClick={() => { setStep(1); setRole(""); }} className="btn btn-ghost btn-xs gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                    </svg>
                    Modifier le rôle
                  </button>
                  <span className="badge badge-soft badge-primary">{role === "freelance" ? "Freelance" : "Client"}</span>
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div className="form-control w-full">
                    <label className="label"><span className="label-text">Prénom</span></label>
                    <input type="text" value={firstName} onChange={(e) => setFirstName(e.target.value)} className="input input-bordered w-full" required />
                  </div>
                  <div className="form-control w-full">
                    <label className="label"><span className="label-text">Nom</span></label>
                    <input type="text" value={lastName} onChange={(e) => setLastName(e.target.value)} className="input input-bordered w-full" required />
                  </div>
                </div>

                <div className="form-control w-full">
                  <label className="label"><span className="label-text">Email</span></label>
                  <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="votre@email.com" className="input input-bordered w-full" required />
                </div>

                <div className="form-control w-full">
                  <label className="label"><span className="label-text">Téléphone <span className="text-base-content/30">(optionnel)</span></span></label>
                  <input type="tel" value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="+225 XX XX XX XX" className="input input-bordered w-full" />
                </div>

                <div className="form-control w-full">
                  <label className="label"><span className="label-text">Mot de passe</span></label>
                  <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder="Minimum 8 caractères" className="input input-bordered w-full" minLength={8} required />
                </div>

                <button type="submit" className="btn btn-primary w-full" disabled={isLoading}>
                  {isLoading ? <span className="loading loading-spinner" /> : "Créer mon compte"}
                </button>
              </form>
            )}

            {step === 1 && role && (
              <div className="flex items-center justify-center py-4">
                <div className="text-center">
                  <p className="text-sm text-base-content/50 mb-3">Rôle sélectionné :</p>
                  <span className="badge badge-lg badge-soft badge-primary mb-3">{role === "freelance" ? "Freelance" : "Client"}</span>
                  <button type="button" onClick={() => setStep(2)} className="btn btn-primary btn-block">Continuer</button>
                  <button type="button" onClick={() => setRole("")} className="btn btn-ghost btn-sm mt-2">Changer de rôle</button>
                </div>
              </div>
            )}

            <p className="text-center text-sm text-base-content/50 mt-4">
              Déjà un compte ?{" "}
              <Link href="/auth/login" className="link link-primary font-medium">Connectez-vous</Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
