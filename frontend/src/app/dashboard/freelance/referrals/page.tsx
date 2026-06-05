"use client";

import { useQuery } from "@tanstack/react-query";
import { formatDate } from "@/lib/utils/format";

export default function ReferralsPage() {
  return (
    <div className="space-y-6 max-w-lg">
      <div>
        <h1 className="text-2xl font-bold">Parrainage</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Parrainez d&apos;autres freelances et gagnez des récompenses
        </p>
      </div>

      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-6 text-center py-12">
          <svg xmlns="http://www.w3.org/2000/svg" className="w-16 h-16 text-base-content/20 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <p className="text-base-content/50">Fonctionnalité à venir</p>
        </div>
      </div>
    </div>
  );
}
