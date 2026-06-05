"use client";

import { useQuery } from "@tanstack/react-query";
import { messageApi } from "@/lib/api/endpoints/messages";
import { formatRelativeDate } from "@/lib/utils/format";
import Link from "next/link";
import { useAuth } from "@/hooks/useAuth";

export default function MessagesPage() {
  const { user } = useAuth();

  const { data, isLoading } = useQuery({
    queryKey: ["conversations"],
    queryFn: () => messageApi.conversations(),
  });

  const conversations = data?.data ?? [];

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Messagerie</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Vos conversations avec clients et freelances
        </p>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center py-12">
          <span className="loading loading-spinner loading-lg text-primary" />
        </div>
      ) : conversations.length === 0 ? (
        <div className="card bg-base-100 border border-base-200">
          <div className="card-body items-center text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" className="w-16 h-16 text-base-content/20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <h3 className="font-semibold mb-1">Aucune conversation</h3>
            <p className="text-sm text-base-content/60">
              Les conversations démarrent automatiquement quand vous
              postulez à un projet ou recevez un devis
            </p>
          </div>
        </div>
      ) : (
        <div className="space-y-2">
          {conversations.map((conv) => {
            const other = user?.role === "client" ? conv.freelance : conv.client;
            const otherName = other
              ? `${other.first_name} ${other.last_name}`
              : "Utilisateur";

            return (
              <Link
                key={conv.id}
                href={`/dashboard/messages/${conv.id}`}
                className="card bg-base-100 border border-base-200 hover:border-primary/30 transition-colors block"
              >
                <div className="card-body p-4">
                  <div className="flex items-center gap-3">
                    <div className="avatar placeholder">
                      <div className="w-10 h-10 rounded-full bg-primary/10 text-primary text-sm font-medium flex items-center justify-center">
                        {otherName.charAt(0)}
                      </div>
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center justify-between">
                        <h3 className="font-medium text-sm truncate">
                          {otherName}
                        </h3>
                        {conv.last_message_at && (
                          <span className="text-xs text-base-content/50 flex-shrink-0">
                            {formatRelativeDate(conv.last_message_at)}
                          </span>
                        )}
                      </div>
                      <p className="text-xs text-base-content/60 truncate mt-0.5">
                        {conv.subject || "Discussion"}
                      </p>
                    </div>
                  </div>
                </div>
              </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}
