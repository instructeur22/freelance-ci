"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { profileApi } from "@/lib/api/endpoints/profile";
import { publicApi } from "@/lib/api/endpoints/public";
import { useState } from "react";

export default function SkillsPage() {
  const queryClient = useQueryClient();

  const { data: profileData } = useQuery({
    queryKey: ["freelance-profile"],
    queryFn: () => profileApi.freelance(),
  });

  const { data: categoriesData } = useQuery({
    queryKey: ["categories"],
    queryFn: () => publicApi.categories(),
  });

  const [selectedSkill, setSelectedSkill] = useState("");
  const [proficiency, setProficiency] = useState("intermediate");

  const skills = profileData?.data?.skills ?? [];
  const categories = categoriesData?.data ?? [];

  const addMutation = useMutation({
    mutationFn: () => profileApi.addSkill(selectedSkill, proficiency),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["freelance-profile"] });
      setSelectedSkill("");
    },
  });

  const removeMutation = useMutation({
    mutationFn: (skillId: string) => profileApi.removeSkill(skillId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["freelance-profile"] });
    },
  });

  return (
    <div className="space-y-6 max-w-2xl">
      <div>
        <h1 className="text-2xl font-bold">Compétences</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Gérez vos compétences professionnelles
        </p>
      </div>

      {/* Add skill */}
      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-5 space-y-3">
          <h2 className="font-semibold">Ajouter une compétence</h2>
          <div className="grid grid-cols-2 gap-3">
            <select
              value={selectedSkill}
              onChange={(e) => setSelectedSkill(e.target.value)}
              className="select select-bordered"
            >
              <option value="">Sélectionner...</option>
              {categories.map((cat) => (
                <optgroup key={cat.id} label={cat.name}>
                  {cat.skills?.map((skill) => (
                    <option key={skill.id} value={skill.id}>
                      {skill.name}
                    </option>
                  ))}
                </optgroup>
              ))}
            </select>
            <select
              value={proficiency}
              onChange={(e) => setProficiency(e.target.value)}
              className="select select-bordered"
            >
              <option value="beginner">Débutant</option>
              <option value="intermediate">Intermédiaire</option>
              <option value="advanced">Avancé</option>
              <option value="expert">Expert</option>
            </select>
          </div>
          <button
            onClick={() => addMutation.mutate()}
            disabled={!selectedSkill || addMutation.isPending}
            className="btn btn-primary"
          >
            Ajouter
          </button>
        </div>
      </div>

      {/* Current skills */}
      {skills.length > 0 && (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body p-5">
            <h2 className="font-semibold mb-4">
              Mes compétences ({skills.length})
            </h2>
            <div className="flex flex-wrap gap-2">
              {skills.map((s) => (
                <div key={s.skill_id} className="badge badge-lg gap-2">
                  {s.skill.name}
                  <span className="text-xs opacity-60">
                    {s.proficiency_level === "beginner"
                      ? "Débutant"
                      : s.proficiency_level === "intermediate"
                        ? "Intermédiaire"
                        : s.proficiency_level === "advanced"
                          ? "Avancé"
                          : "Expert"}
                  </span>
                  <button
                    onClick={() => removeMutation.mutate(s.skill_id)}
                    className="ml-1 hover:text-error"
                  >
                    ×
                  </button>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
