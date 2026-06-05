import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Politique de confidentialité | Freelance CI",
  description: "Politique de confidentialité de Freelance CI - Comment nous protégeons vos données.",
};

export default function PrivacyPage() {
  return (
    <div className="container mx-auto px-4 py-16 max-w-3xl">
      <h1 className="text-3xl font-bold mb-6">
        Politique de confidentialité
      </h1>

      <div className="space-y-6 text-sm leading-relaxed">
        <section>
          <h2 className="text-xl font-semibold mb-2">
            1. Données collectées
          </h2>
          <p>
            Nous collectons les informations suivantes : nom, prénom, adresse
            email, numéro de téléphone, photo de profil, compétences,
            portfolio, et informations de paiement.
          </p>
        </section>

        <section>
          <h2 className="text-xl font-semibold mb-2">
            2. Utilisation des données
          </h2>
          <p>Vos données sont utilisées pour :</p>
          <ul className="list-disc pl-6 mt-2 space-y-1">
            <li>Créer et gérer votre compte</li>
            <li>Faciliter les mises en relation</li>
            <li>Traiter les paiements via Genius Pay</li>
            <li>Vous envoyer des notifications</li>
            <li>Améliorer nos services</li>
          </ul>
        </section>

        <section>
          <h2 className="text-xl font-semibold mb-2">
            3. Partage des données
          </h2>
          <p>
            Vos données ne sont jamais vendues à des tiers. Elles sont
            partagées uniquement avec Genius Pay pour le traitement des
            paiements et avec Supabase pour l&apos;authentification.
          </p>
        </section>

        <section>
          <h2 className="text-xl font-semibold mb-2">
            4. Vos droits
          </h2>
          <p>
            Conformément à la loi ivoirienne sur la protection des données,
            vous avez le droit d&apos;accès, de rectification et de
            suppression de vos données. Contactez-nous à
            contact@freelance-ci.com.
          </p>
        </section>
      </div>
    </div>
  );
}
