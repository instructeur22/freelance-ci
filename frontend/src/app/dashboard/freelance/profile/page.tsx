"use client";

import { useAuth } from "@/hooks/useAuth";
import { useState } from "react";
import { profileApi } from "@/lib/api/endpoints/profile";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";

export default function FreelanceProfilePage() {
  const { user } = useAuth();
  const queryClient = useQueryClient();

  const { data: profileData } = useQuery({
    queryKey: ["freelance-profile"],
    queryFn: () => profileApi.freelance(),
  });

  const [form, setForm] = useState({
    professional_title: "",
    tagline: "",
    daily_rate_xof: "",
    experience_level: "",
  });

  const [initialized, setInitialized] = useState(false);
  if (!initialized && profileData?.data) {
    const p = profileData.data;
    setForm({
      professional_title: p.professional_title || "",
      tagline: p.tagline || "",
      daily_rate_xof: p.daily_rate_xof?.toString() || "",
      experience_level: p.experience_level || "",
    });
    setInitialized(true);
  }

  const mutation = useMutation({
    mutationFn: (data: typeof form) =>
      profileApi.updateFreelance({
        professional_title: data.professional_title,
        tagline: data.tagline,
        daily_rate_xof: data.daily_rate_xof ? Number(data.daily_rate_xof) : null,
        experience_level: data.experience_level,
      } as any),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["freelance-profile"] });
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    mutation.mutate(form);
  };

  return (
    <div className="max-w-2xl space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Profil freelance</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Optimisez votre profil pour attirer plus de clients
        </p>
      </div>

      <form onSubmit={handleSubmit} className="card bg-base-100 border border-base-200">
        <div className="card-body p-6 space-y-4">
          <div className="form-control">
            <label className="label">
              <span className="label-text">Titre professionnel</span>
            </label>
            <input
              value={form.professional_title}
              onChange={(e) => setForm({ ...form, professional_title: e.target.value })}
              placeholder="Ex: Développeur Full Stack"
              className="input input-bordered"
            />
          </div>

          <div className="form-control">
            <label className="label">
              <span className="label-text">Tagline</span>
            </label>
            <input
              value={form.tagline}
              onChange={(e) => setForm({ ...form, tagline: e.target.value })}
              placeholder="Une courte phrase qui vous décrit"
              className="input input-bordered"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="form-control">
              <label className="label">
                <span className="label-text">Taux journalier (FCFA)</span>
              </label>
              <input
                type="number"
                value={form.daily_rate_xof}
                onChange={(e) => setForm({ ...form, daily_rate_xof: e.target.value })}
                placeholder="Ex: 50000"
                className="input input-bordered"
              />
            </div>
            <div className="form-control">
              <label className="label">
                <span className="label-text">Niveau d'expérience</span>
              </label>
              <select
                value={form.experience_level}
                onChange={(e) => setForm({ ...form, experience_level: e.target.value })}
                className="select select-bordered"
              >
                <option value="">Sélectionner</option>
                <option value="junior">Junior</option>
                <option value="intermediate">Intermédiaire</option>
                <option value="senior">Senior</option>
                <option value="expert">Expert</option>
              </select>
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
