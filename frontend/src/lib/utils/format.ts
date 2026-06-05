import type { ClassValue } from "clsx";
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatPrice(
  amount: number,
  currency: string = "XOF"
): string {
  return new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
}

export function formatDate(date: string | null): string {
  if (!date) return "-";
  return new Intl.DateTimeFormat("fr-FR", {
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(new Date(date));
}

export function formatRelativeDate(date: string | null): string {
  if (!date) return "";
  const now = new Date();
  const d = new Date(date);
  const diffMs = now.getTime() - d.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return "À l'instant";
  if (diffMins < 60) return `Il y a ${diffMins} min`;
  if (diffHours < 24) return `Il y a ${diffHours}h`;
  if (diffDays < 7) return `Il y a ${diffDays} jours`;
  return formatDate(date);
}

export function getInitials(firstName: string, lastName: string): string {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
}

export function getExperienceLabel(level: string | null): string {
  const labels: Record<string, string> = {
    junior: "Junior",
    intermediate: "Intermédiaire",
    senior: "Senior",
    expert: "Expert",
  };
  return level ? labels[level] || level : "Non spécifié";
}

export function getProjectStatusLabel(status: string): string {
  const labels: Record<string, string> = {
    open: "Ouvert",
    in_progress: "En cours",
    completed: "Terminé",
    cancelled: "Annulé",
  };
  return labels[status] || status;
}

export function getContractStatusLabel(status: string): string {
  const labels: Record<string, string> = {
    pending: "En attente de signature",
    active: "Actif",
    completed: "Terminé",
    cancelled: "Annulé",
    disputed: "Litige",
  };
  return labels[status] || status;
}
