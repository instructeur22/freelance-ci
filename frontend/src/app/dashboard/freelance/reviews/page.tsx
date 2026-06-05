"use client";

import { useQuery } from "@tanstack/react-query";
import { reviewApi } from "@/lib/api/endpoints/reviews";

export default function ReviewsPage() {
  // Show placeholder for now - actual implementation needs the reviews listing endpoint
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Avis reçus</h1>
        <p className="text-base-content/70 text-sm mt-1">
          Consultez les avis laissés par vos clients
        </p>
      </div>

      <div className="card bg-base-100 border border-base-200">
        <div className="card-body items-center text-center py-12">
          <svg xmlns="http://www.w3.org/2000/svg" className="w-16 h-16 text-base-content/20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
          </svg>
          <p className="text-base-content/50">Aucun avis pour le moment</p>
        </div>
      </div>
    </div>
  );
}
