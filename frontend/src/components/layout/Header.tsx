"use client";

import Link from "next/link";
import { useAuth } from "@/hooks/useAuth";
import { useQuery } from "@tanstack/react-query";
import { messageApi } from "@/lib/api/endpoints/messages";
import { notificationApi } from "@/lib/api/endpoints/notifications";
import { useState } from "react";
import { Logo } from "@/components/shared/Logo";

export function Header() {
  const { user, isAuthenticated, logout } = useAuth();
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  const { data: messagesData } = useQuery({
    queryKey: ["conversations"],
    queryFn: () => messageApi.conversations(),
    enabled: isAuthenticated,
  });

  const { data: notifsData } = useQuery({
    queryKey: ["notifications"],
    queryFn: () => notificationApi.list(),
    enabled: isAuthenticated,
  });

  const conversations = messagesData?.data ?? [];
  const unreadMessages = conversations.filter(
    (c) => c.last_message_at && !c.messages?.length
  ).length;

  const notifications = notifsData?.data ?? [];
  const unreadCount = notifications.filter((n) => !n.is_read).length;

  const navLinks = [
    { href: "/projects", label: "Projets" },
    { href: "/freelancers", label: "Freelances" },
    { href: "/categories", label: "Catégories" },
  ];

  return (
    <header className="bg-neutral text-neutral-content sticky top-0 z-50 shadow-lg">
      <div className="navbar container mx-auto px-4 min-h-14">
        <div className="navbar-start gap-2">
          <Logo size="sm" />
        </div>

        <div className="navbar-center hidden md:flex">
          <nav className="flex items-center gap-1">
            {navLinks.map((l) => (
              <Link
                key={l.href}
                href={l.href}
                className="btn btn-ghost btn-sm text-neutral-content/80 hover:text-neutral-content"
              >
                {l.label}
              </Link>
            ))}
          </nav>
        </div>

        <div className="navbar-end gap-1">
          {isAuthenticated && user ? (
            <>
              <Link
                href="/dashboard/messages"
                className="btn btn-ghost btn-sm btn-square text-neutral-content/80 hover:text-neutral-content relative"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                {unreadMessages > 0 && (
                  <span className="badge badge-primary badge-xs absolute -top-1 -right-1">{unreadMessages}</span>
                )}
              </Link>

              <div className="dropdown dropdown-end">
                <button type="button" className="btn btn-ghost btn-sm btn-square text-neutral-content/80 hover:text-neutral-content relative">
                  <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  {unreadCount > 0 && (
                    <span className="badge badge-error badge-xs absolute -top-1 -right-1">{unreadCount}</span>
                  )}
                </button>
                <ul className="dropdown-content menu bg-base-100 rounded-box z-50 w-72 p-2 shadow-xl border border-base-200 text-base-content">
                  <li className="menu-title"><span>Notifications</span></li>
                  {notifications.length === 0 ? (
                    <li><p className="text-sm text-base-content/50 text-center py-4">Aucune notification</p></li>
                  ) : (
                    notifications.slice(0, 5).map((n) => (
                      <li key={n.id}>
                        <Link href={n.action_url || "/dashboard/notifications"} className={!n.is_read ? "font-medium" : ""}>
                          <p className="text-sm">{n.title}</p>
                          {n.body && <p className="text-xs text-base-content/60 line-clamp-1">{n.body}</p>}
                        </Link>
                      </li>
                    ))
                  )}
                  {notifications.length > 0 && (
                    <li><Link href="/dashboard/notifications" className="text-center text-sm text-primary">Voir toutes</Link></li>
                  )}
                </ul>
              </div>

              <div className="dropdown dropdown-end">
                <div tabIndex={0} role="button" className="btn btn-ghost btn-sm btn-circle avatar">
                  <div className="w-7 h-7 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">
                    {user.first_name?.charAt(0)}{user.last_name?.charAt(0)}
                  </div>
                </div>
                <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-50 w-52 p-2 shadow-xl border border-base-200 text-base-content">
                  <li className="menu-title"><span>{user.first_name} {user.last_name}</span></li>
                  <li><Link href="/dashboard">Tableau de bord</Link></li>
                  <li><Link href={user.role === "freelance" ? "/dashboard/freelance/profile" : "/dashboard/client/profile"}>Mon profil</Link></li>
                  <li className="menu-title"><span>Session</span></li>
                  <li><button onClick={logout} type="button">Déconnexion</button></li>
                </ul>
              </div>
            </>
          ) : (
            <div className="hidden md:flex items-center gap-2">
              <Link href="/auth/login" className="btn btn-ghost btn-sm text-neutral-content/80 hover:text-neutral-content">Connexion</Link>
              <Link href="/auth/register" className="btn btn-primary btn-sm shadow-sm">Inscription</Link>
            </div>
          )}

          <button type="button" className="btn btn-ghost md:hidden btn-square text-neutral-content/80" onClick={() => setIsMenuOpen(!isMenuOpen)}>
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              {isMenuOpen ? (
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              ) : (
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
              )}
            </svg>
          </button>
        </div>
      </div>

      <div className={`md:hidden w-full bg-neutral border-t border-neutral/30 overflow-hidden transition-all duration-200 ${isMenuOpen ? "max-h-96" : "max-h-0"}`}>
        <ul className="menu menu-sm px-4 py-2">
          {navLinks.map((l) => (
            <li key={l.href}>
              <Link href={l.href} className="text-neutral-content/80 hover:text-neutral-content" onClick={() => setIsMenuOpen(false)}>
                {l.label}
              </Link>
            </li>
          ))}
          {!isAuthenticated && (
            <>
              <li className="menu-title text-neutral-content/40 mt-2"><span>Compte</span></li>
              <li><Link href="/auth/login" className="text-neutral-content/80 hover:text-neutral-content" onClick={() => setIsMenuOpen(false)}>Connexion</Link></li>
              <li><Link href="/auth/register" className="text-primary font-medium" onClick={() => setIsMenuOpen(false)}>Inscription</Link></li>
            </>
          )}
        </ul>
      </div>
    </header>
  );
}
