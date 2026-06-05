"use client";

import { useAuth } from "@/hooks/useAuth";
import { useState } from "react";
import { profileApi } from "@/lib/api/endpoints/profile";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";

export default function ClientProfilePage() {
  const { user } = useAuth();
  const queryClient = useQueryClient();

  const { data: profileData } = useQuery({
    queryKey: ["client-profile"],
    queryFn: () => profileApi.client(),
  });

  const [form, setForm] = useState({
    company_name: "",
    company_website: "",
    company_size: "",
    industry: "",
  });

  const [initialized, setInitialized] = useState(false);
  if (!initialized && profileData?.data) {
    const p = profileData.data;
    setForm({
      company_name: p.company_name || "",
      company_website: p.company_website || "",
      company_size: p.company_size || "",
      industry: p.industry || "",
    });
    setInitialized(true);
  }

  const mutation = useMutation({
    mutationFn: (data: typeof form) => profileApi.updateClient(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["client-profile"] });
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    mutation.mutate(form);
  };

  return (
    <div className="max-w-2xl space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Profil client</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gérez les informations de votre entreprise
        </p>
      </div>

      <form onSubmit={handleSubmit} className="card bg-base-100 border border-base-200">
        <div className="card-body p-6 space-y-4">
          <div className="form-control">
            <label className="label">
              <span className="label-text">Nom de l'entreprise</span>
            </label>
            <input
              value={form.company_name}
              onChange={(e) =>
                setForm({ ...form, company_name: e.target.value })
              }
              placeholder="Votre entreprise"
              className="input input-bordered"
            />
          </div>

          <div className="form-control">
            <label className="label">
              <span className="label-text">Site web</span>
            </label>
            <input
              value={form.company_website}
              onChange={(e) =>
                setForm({ ...form, company_website: e.target.value })
              }
              placeholder="https://exemple.com"
              className="input input-bordered"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="form-control">
              <label className="label">
                <span className="label-text">Taille de l'entreprise</span>
              </label>
              <select
                value={form.company_size}
                onChange={(e) =>
                  setForm({ ...form, company_size: e.target.value })
                }
                className="select select-bordered"
              >
                <option value="">Sélectionner</option>
                <option value="1-10">1-10 employés</option>
                <option value="11-50">11-50 employés</option>
                <option value="51-200">51-200 employés</option>
                <option value="200+">200+ employés</option>
              </select>
            </div>
            <div className="form-control">
              <label className="label">
                <span className="label-text">Secteur d'activité</span>
              </label>
              <input
                value={form.industry}
                onChange={(e) =>
                  setForm({ ...form, industry: e.target.value })
                }
                placeholder="Ex: Tech, Finance..."
                className="input input-bordered"
              />
            </div>
          </div>

          <button
            type="submit"
            className="btn btn-primary"
            disabled={mutation.isPending}
          >
            {mutation.isPending ? (
              <span className="loading loading-spinner loading-sm" />
            ) : (
              "Enregistrer"
            )}
          </button>

          {mutation.isSuccess && (
            <div className="alert alert-success text-sm">
              Profil mis à jour avec succès
            </div>
          )}
        </div>
      </form>
    </div>
  );
}
