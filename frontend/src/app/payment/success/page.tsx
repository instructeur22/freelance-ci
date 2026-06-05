import Link from "next/link";

export default function PaymentSuccessPage() {
  return (
    <div className="min-h-[60vh] flex items-center justify-center px-4">
      <div className="text-center max-w-md">
        <div className="w-20 h-20 rounded-full bg-success/10 text-success flex items-center justify-center mx-auto mb-6">
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
              d="M5 13l4 4L19 7"
            />
          </svg>
        </div>
        <h1 className="text-2xl font-bold mb-2">Paiement réussi !</h1>
        <p className="text-base-content/70 mb-8">
          Votre paiement a été effectué avec succès. Les fonds sont
          maintenant sous séquestre en attendant la validation des jalons.
        </p>
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <Link href="/dashboard/contracts" className="btn btn-primary">
            Voir mes contrats
          </Link>
          <Link href="/dashboard" className="btn btn-outline">
            Tableau de bord
          </Link>
        </div>
      </div>
    </div>
  );
}
