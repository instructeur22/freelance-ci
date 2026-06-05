import Link from "next/link";

export default function PaymentCancelPage() {
  return (
    <div className="min-h-[60vh] flex items-center justify-center px-4">
      <div className="text-center max-w-md">
        <div className="w-20 h-20 rounded-full bg-warning/10 text-warning flex items-center justify-center mx-auto mb-6">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="w-10 h-10"
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
        </div>
        <h1 className="text-2xl font-bold mb-2">Paiement annulé</h1>
        <p className="text-base-content/70 mb-8">
          Le paiement a été annulé. Aucun montant n&apos;a été débité.
          Vous pouvez réessayer quand vous le souhaitez.
        </p>
        <Link href="/dashboard" className="btn btn-primary">
          Retour au tableau de bord
        </Link>
      </div>
    </div>
  );
}
