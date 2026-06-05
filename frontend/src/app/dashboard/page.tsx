"use client";

import { useAuth } from "@/hooks/useAuth";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { adminApi } from "@/lib/api/endpoints/admin";
import { contractApi } from "@/lib/api/endpoints/contracts";
import { paymentApi } from "@/lib/api/endpoints/payments";
import { walletApi } from "@/lib/api/endpoints/wallet";
import { formatPrice } from "@/lib/utils/format";

export default function DashboardHome() {
  const { user } = useAuth();

  if (!user) return null;

  if (user.role === "admin") return <AdminDashboard />;
  if (user.role === "freelance") return <FreelanceDashboard />;
  return <ClientDashboard />;
}

function StatCard({ label, value, icon, color = "ghost" }: { label: string; value: string; icon: string; color?: string }) {
  const colorMap: Record<string, string> = {
    primary: "bg-primary/10 text-primary",
    success: "bg-success/10 text-success",
    warning: "bg-warning/10 text-warning",
    error: "bg-error/10 text-error",
    ghost: "bg-base-200 text-base-content/60",
  };
  const bgColor = colorMap[color] || colorMap.ghost;

  return (
    <div className="card bg-base-100 border border-base-300 shadow-sm">
      <div className="card-body p-4">
        <div className="flex items-center gap-3">
          <div className={`w-10 h-10 rounded-lg ${bgColor} flex items-center justify-center shrink-0`}>
            <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d={icon} />
            </svg>
          </div>
          <div className="min-w-0">
            <p className="text-lg font-bold truncate">{value}</p>
            <p className="text-xs text-base-content/50">{label}</p>
          </div>
        </div>
      </div>
    </div>
  );
}

function ClientDashboard() {
  const { data: contracts } = useQuery({ queryKey: ["contracts"], queryFn: () => contractApi.list() });
  const { data: payments } = useQuery({ queryKey: ["payments"], queryFn: () => paymentApi.list() });

  const contractList = contracts?.data ?? [];
  const activeContracts = contractList.filter((c) => c.status === "active" || c.status === "pending");
  const completedContracts = contractList.filter((c) => c.status === "completed");
  const paymentList = payments?.data ?? [];
  const totalSpent = paymentList.reduce((sum, p) => sum + (p.status === "completed" ? p.amount : 0), 0);

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Tableau de bord client</h1>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Projets actifs" value={activeContracts.length.toString()} icon="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        <StatCard label="Terminés" value={completedContracts.length.toString()} icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" color="success" />
        <StatCard label="Total dépensé" value={formatPrice(totalSpent)} icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" color="primary" />
        <StatCard label="Paiements" value={paymentList.length.toString()} icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </div>

      <div className="grid lg:grid-cols-2 gap-6">
        <div className="card bg-base-100 border border-base-300 shadow-sm">
          <div className="card-body p-5">
            <h2 className="font-semibold mb-4">Actions rapides</h2>
            <div className="space-y-2">
              <Link href="/dashboard/client/projects/new" className="btn btn-primary w-full">Publier un projet</Link>
              <Link href="/dashboard/client/projects" className="btn btn-outline w-full">Voir mes projets</Link>
            </div>
          </div>
        </div>

        <div className="card bg-base-100 border border-base-300 shadow-sm">
          <div className="card-body p-5">
            <h2 className="font-semibold mb-4">Contrats récents</h2>
            {contractList.length === 0 ? (
              <p className="text-sm text-base-content/50 text-center py-6">Aucun contrat pour le moment</p>
            ) : (
              <div className="space-y-1">
                {contractList.slice(0, 5).map((c) => (
                  <Link key={c.id} href={`/dashboard/client/contracts/${c.id}`} className="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 transition-colors">
                    <span className="text-sm truncate flex-1">{c.title}</span>
                    <span className={`badge badge-sm ${c.status === "active" ? "badge-success" : c.status === "pending" ? "badge-warning" : c.status === "completed" ? "badge-info" : "badge-ghost"}`}>
                      {c.status === "active" ? "Actif" : c.status === "pending" ? "En attente" : c.status === "completed" ? "Terminé" : "Annulé"}
                    </span>
                  </Link>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

function FreelanceDashboard() {
  const { data: contracts } = useQuery({ queryKey: ["contracts"], queryFn: () => contractApi.list() });
  const { data: wallet } = useQuery({ queryKey: ["wallet"], queryFn: () => walletApi.get() });

  const contractList = contracts?.data ?? [];
  const activeContracts = contractList.filter((c) => c.status === "active" || c.status === "pending");
  const completedContracts = contractList.filter((c) => c.status === "completed");

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Tableau de bord freelance</h1>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Contrats actifs" value={activeContracts.length.toString()} icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        <StatCard label="Terminés" value={completedContracts.length.toString()} icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" color="success" />
        <StatCard label="Disponible" value={wallet?.data ? formatPrice(wallet.data.available_xof) : "0 FCFA"} icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" color="primary" />
        <StatCard label="En attente" value={wallet?.data ? formatPrice(wallet.data.pending_xof) : "0 FCFA"} icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" color="warning" />
      </div>

      <div className="grid lg:grid-cols-2 gap-6">
        <div className="card bg-base-100 border border-base-300 shadow-sm">
          <div className="card-body p-5">
            <h2 className="font-semibold mb-4">Actions rapides</h2>
            <div className="space-y-2">
              <Link href="/projects" className="btn btn-primary w-full">Parcourir les projets</Link>
              <Link href="/dashboard/freelance/quotes" className="btn btn-outline w-full">Mes devis</Link>
              <Link href="/dashboard/freelance/wallet" className="btn btn-outline w-full">Mon porte-monnaie</Link>
            </div>
          </div>
        </div>

        <div className="card bg-base-100 border border-base-300 shadow-sm">
          <div className="card-body p-5">
            <h2 className="font-semibold mb-4">Contrats récents</h2>
            {contractList.length === 0 ? (
              <p className="text-sm text-base-content/50 text-center py-6">Aucun contrat pour le moment</p>
            ) : (
              <div className="space-y-1">
                {contractList.slice(0, 5).map((c) => (
                  <Link key={c.id} href={`/dashboard/freelance/contracts/${c.id}`} className="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 transition-colors">
                    <span className="text-sm truncate flex-1">{c.title}</span>
                    <span className={`badge badge-sm ${c.status === "active" ? "badge-success" : c.status === "pending" ? "badge-warning" : c.status === "completed" ? "badge-info" : "badge-ghost"}`}>
                      {c.status === "active" ? "Actif" : c.status === "pending" ? "En attente" : c.status === "completed" ? "Terminé" : "Annulé"}
                    </span>
                  </Link>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

function AdminDashboard() {
  const { data } = useQuery({ queryKey: ["admin-dashboard"], queryFn: () => adminApi.dashboard() });
  const stats = data?.data;

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Administration</h1>

      {stats ? (
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard label="Utilisateurs" value={stats.total_users.toString()} icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
          <StatCard label="Freelances" value={stats.total_freelances.toString()} icon="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          <StatCard label="Projets" value={stats.total_projects.toString()} icon="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          <StatCard label="Revenus" value={formatPrice(stats.total_revenue)} icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" color="primary" />
          <StatCard label="Contrats" value={stats.total_contracts.toString()} icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          <StatCard label="Vérifications" value={stats.pending_verifications.toString()} icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" color={stats.pending_verifications > 0 ? "warning" : "success"} />
          <StatCard label="Litiges" value={stats.open_disputes.toString()} icon="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" color={stats.open_disputes > 0 ? "error" : "success"} />
          <StatCard label="Clients" value={stats.total_clients.toString()} icon="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </div>
      ) : (
        <div className="flex items-center justify-center py-12"><span className="loading loading-spinner loading-lg text-primary" /></div>
      )}

      <div className="grid lg:grid-cols-3 gap-4">
        <Link href="/dashboard/admin/verifications" className="card bg-base-100 border border-base-300 hover:border-primary/30 transition-colors shadow-sm">
          <div className="card-body p-5"><h3 className="font-semibold">Vérifications</h3><p className="text-sm text-base-content/60">Documents en attente</p></div>
        </Link>
        <Link href="/dashboard/admin/reports" className="card bg-base-100 border border-base-300 hover:border-primary/30 transition-colors shadow-sm">
          <div className="card-body p-5"><h3 className="font-semibold">Signalements</h3><p className="text-sm text-base-content/60">Signalements utilisateurs</p></div>
        </Link>
        <Link href="/dashboard/admin/disputes" className="card bg-base-100 border border-base-300 hover:border-primary/30 transition-colors shadow-sm">
          <div className="card-body p-5"><h3 className="font-semibold">Litiges</h3><p className="text-sm text-base-content/60">Litiges en cours</p></div>
        </Link>
      </div>
    </div>
  );
}
