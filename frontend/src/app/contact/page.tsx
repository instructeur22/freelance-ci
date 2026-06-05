import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Contact | Freelance CI",
  description: "Contactez l'équipe Freelance CI - Support, questions, partenariats.",
};

export default function ContactPage() {
  return (
    <div className="container mx-auto px-4 py-16 max-w-2xl">
      <h1 className="text-3xl font-bold mb-6">Contactez-nous</h1>
      <p className="text-base-content/70 mb-10">
        Une question, un problème ou une suggestion ? Notre équipe est là
        pour vous aider.
      </p>

      <div className="grid gap-6 sm:grid-cols-2 mb-12">
        <div className="card bg-base-200/50 border border-base-200">
          <div className="card-body items-center text-center p-6">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="w-8 h-8 text-primary mb-3"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
            <h3 className="font-semibold">Email</h3>
            <p className="text-sm text-base-content/70 mt-1">
              contact@freelance-ci.com
            </p>
          </div>
        </div>

        <div className="card bg-base-200/50 border border-base-200">
          <div className="card-body items-center text-center p-6">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="w-8 h-8 text-primary mb-3"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
              />
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
              />
            </svg>
            <h3 className="font-semibold">Adresse</h3>
            <p className="text-sm text-base-content/70 mt-1">
              Abidjan, Cocody, Côte d&apos;Ivoire
            </p>
          </div>
        </div>
      </div>

      <div className="card bg-base-100 border border-base-200">
        <div className="card-body p-6">
          <h2 className="text-lg font-semibold mb-4">
            Envoyez-nous un message
          </h2>
          <form className="space-y-4">
            <div className="grid sm:grid-cols-2 gap-4">
              <div className="form-control">
                <label className="label">
                  <span className="label-text">Nom</span>
                </label>
                <input
                  type="text"
                  placeholder="Votre nom"
                  className="input input-bordered"
                  required
                />
              </div>
              <div className="form-control">
                <label className="label">
                  <span className="label-text">Email</span>
                </label>
                <input
                  type="email"
                  placeholder="votre@email.com"
                  className="input input-bordered"
                  required
                />
              </div>
            </div>
            <div className="form-control">
              <label className="label">
                <span className="label-text">Sujet</span>
              </label>
              <input
                type="text"
                placeholder="Sujet de votre message"
                className="input input-bordered"
                required
              />
            </div>
            <div className="form-control">
              <label className="label">
                <span className="label-text">Message</span>
              </label>
              <textarea
                placeholder="Votre message..."
                className="textarea textarea-bordered h-32"
                required
              />
            </div>
            <button type="submit" className="btn btn-primary w-full">
              Envoyer
            </button>
          </form>

          <p className="text-xs text-base-content/50 mt-4 text-center">
            Ou écrivez-nous directement à{" "}
            <Link
              href="mailto:contact@freelance-ci.com"
              className="link link-primary"
            >
              contact@freelance-ci.com
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
