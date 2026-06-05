"use client";

import { useState, useRef } from "react";
import { useRouter } from "next/navigation";
import { projectApi } from "@/lib/api/endpoints/projects";
import { useQuery } from "@tanstack/react-query";
import { publicApi } from "@/lib/api/endpoints/public";

interface FileItem {
  file: File;
  preview?: string;
}

export default function NewProjectPage() {
  const router = useRouter();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [files, setFiles] = useState<FileItem[]>([]);

  const { data: categoriesData } = useQuery({
    queryKey: ["categories"],
    queryFn: () => publicApi.categories(),
  });

  const [form, setForm] = useState({
    title: "",
    description: "",
    category_id: "",
    budget_min: "",
    budget_max: "",
    duration_days: "",
    experience_level: "intermediate",
    is_remote: true,
    location: "",
    is_urgent: false,
  });

  const categories = categoriesData?.data ?? [];

  const handleChange = (
    e: React.ChangeEvent<
      HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
    >
  ) => {
    const { name, value, type } = e.target;
    setForm((prev) => ({
      ...prev,
      [name]:
        type === "checkbox"
          ? (e.target as HTMLInputElement).checked
          : value,
    }));
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selected = Array.from(e.target.files || []);
    const newFiles = selected.map((file) => ({
      file,
      preview: file.type.startsWith("image/")
        ? URL.createObjectURL(file)
        : undefined,
    }));
    setFiles((prev) => [...prev, ...newFiles]);
    if (fileInputRef.current) fileInputRef.current.value = "";
  };

  const removeFile = (index: number) => {
    setFiles((prev) => {
      const item = prev[index];
      if (item.preview) URL.revokeObjectURL(item.preview);
      return prev.filter((_, i) => i !== index);
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setIsSubmitting(true);

    try {
      const res = await projectApi.create({
        title: form.title,
        description: form.description,
        category_id: form.category_id,
        budget_min: form.budget_min ? Number(form.budget_min) : null,
        budget_max: form.budget_max ? Number(form.budget_max) : null,
        duration_days: form.duration_days ? Number(form.duration_days) : null,
        experience_level: form.experience_level || null,
        is_remote: form.is_remote,
        location: form.location || null,
        is_urgent: form.is_urgent,
      } as any);

      const projectId = res?.data?.id;
      if (!projectId) throw new Error("Impossible de récupérer l'ID du projet");

      // Upload files after project creation
      for (const item of files) {
        const fd = new FormData();
        fd.append("file", item.file);
        fd.append("file_name", item.file.name);
        if (item.file.type) fd.append("file_type", item.file.type);
        fd.append("file_size", String(item.file.size));
        await projectApi.addFile(projectId, fd);
      }

      router.push("/dashboard/client/projects");
    } catch (err) {
      setError(
        err instanceof Error ? err.message : "Erreur lors de la création"
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="max-w-2xl">
      <div className="mb-6">
        <h1 className="text-2xl font-bold">Nouveau projet</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Décrivez votre projet pour attirer les meilleurs freelances
        </p>
      </div>

      <form onSubmit={handleSubmit} className="card bg-base-100 border border-base-200">
        <div className="card-body p-6 space-y-4">
          {error && (
            <div className="alert alert-error text-sm">{error}</div>
          )}

          <div className="form-control">
            <label className="label">
              <span className="label-text">Titre du projet *</span>
            </label>
            <input
              name="title"
              value={form.title}
              onChange={handleChange}
              placeholder="Ex: Développement site web e-commerce"
              className="input input-bordered"
              required
            />
          </div>

          <div className="form-control">
            <label className="label">
              <span className="label-text">Description *</span>
            </label>
            <textarea
              name="description"
              value={form.description}
              onChange={handleChange}
              placeholder="Décrivez votre projet en détail..."
              className="textarea textarea-bordered h-32"
              required
            />
          </div>

          <div className="form-control">
            <label className="label">
              <span className="label-text">Catégorie</span>
            </label>
            <select
              name="category_id"
              value={form.category_id}
              onChange={handleChange}
              className="select select-bordered"
            >
              <option value="">Sélectionner une catégorie</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.name}
                </option>
              ))}
            </select>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="form-control">
              <label className="label">
                <span className="label-text">Budget min (FCFA)</span>
              </label>
              <input
                name="budget_min"
                type="number"
                value={form.budget_min}
                onChange={handleChange}
                placeholder="Ex: 50000"
                className="input input-bordered"
              />
            </div>
            <div className="form-control">
              <label className="label">
                <span className="label-text">Budget max (FCFA)</span>
              </label>
              <input
                name="budget_max"
                type="number"
                value={form.budget_max}
                onChange={handleChange}
                placeholder="Ex: 200000"
                className="input input-bordered"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div className="form-control">
              <label className="label">
                <span className="label-text">Durée estimée (jours)</span>
              </label>
              <input
                name="duration_days"
                type="number"
                value={form.duration_days}
                onChange={handleChange}
                placeholder="Ex: 30"
                className="input input-bordered"
              />
            </div>
            <div className="form-control">
              <label className="label">
                <span className="label-text">Niveau d'expérience</span>
              </label>
              <select
                name="experience_level"
                value={form.experience_level}
                onChange={handleChange}
                className="select select-bordered"
              >
                <option value="junior">Junior</option>
                <option value="intermediate">Intermédiaire</option>
                <option value="senior">Senior</option>
                <option value="expert">Expert</option>
              </select>
            </div>
          </div>

          <div className="form-control">
            <label className="label">
              <span className="label-text">Localisation</span>
            </label>
            <input
              name="location"
              value={form.location}
              onChange={handleChange}
              placeholder="Ex: Abidjan, Cocody"
              className="input input-bordered"
            />
          </div>

          <div className="flex gap-4">
            <label className="label cursor-pointer gap-2">
              <input
                name="is_remote"
                type="checkbox"
                checked={form.is_remote}
                onChange={handleChange}
                className="checkbox checkbox-primary"
              />
              <span className="label-text">Projet à distance</span>
            </label>
            <label className="label cursor-pointer gap-2">
              <input
                name="is_urgent"
                type="checkbox"
                checked={form.is_urgent}
                onChange={handleChange}
                className="checkbox checkbox-secondary"
              />
              <span className="label-text">Urgent</span>
            </label>
          </div>

          {/* File upload */}
          <div className="form-control">
            <label className="label">
              <span className="label-text">Fichiers joints (optionnel)</span>
            </label>
            <input
              ref={fileInputRef}
              type="file"
              multiple
              onChange={handleFileSelect}
              className="file-input file-input-bordered file-input-sm"
              accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip,.rar"
            />
            {files.length > 0 && (
              <div className="flex flex-wrap gap-2 mt-3">
                {files.map((item, index) => (
                  <div
                    key={index}
                    className="flex items-center gap-2 bg-base-200 rounded-lg px-3 py-2"
                  >
                    {item.preview ? (
                      <img
                        src={item.preview}
                        alt=""
                        className="w-8 h-8 object-cover rounded"
                      />
                    ) : (
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-5 h-5 text-base-content/40"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                      </svg>
                    )}
                    <span className="text-sm truncate max-w-[160px]">
                      {item.file.name}
                    </span>
                    <button
                      type="button"
                      onClick={() => removeFile(index)}
                      className="btn btn-ghost btn-xs text-error"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M6 18L18 6M6 6l12 12"
                        />
                      </svg>
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>

          <div className="flex gap-3 pt-2">
            <button
              type="button"
              onClick={() => router.back()}
              className="btn btn-ghost"
            >
              Annuler
            </button>
            <button
              type="submit"
              className="btn btn-primary flex-1"
              disabled={isSubmitting}
            >
              {isSubmitting ? (
                <span className="loading loading-spinner" />
              ) : (
                "Publier le projet"
              )}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
