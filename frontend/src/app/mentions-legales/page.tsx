import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Mentions légales | Freelance CI",
  description: "Mentions légales de Freelance CI, plateforme de freelances en Côte d'Ivoire.",
};

export default function LegalPage() {
  return (
    <div className="container mx-auto px-4 py-16 max-w-3xl">
      <h1 className="text-3xl font-bold mb-6">Mentions légales</h1>

      <div className="space-y-6 text-sm leading-relaxed">
        <section>
          <h2 className="text-xl font-semibold mb-2">1. Éditeur</h2>
          <p>
            Le site Freelance CI est édité par la société Freelance CI SAS,
            immatriculée au registre du commerce d&apos;Abidjan sous le numéro
            RC-12345.
          </p>
          <p className="mt-2">Adresse : Abidjan, Cocody, Côte d&apos;Ivoire</p>
          <p>Email : contact@freelance-ci.com</p>
        </section>

        <section>
          <h2 className="text-xl font-semibold mb-2">2. Hébergement</h2>
          <p>
            Le site est hébergé par Vercel Inc., 340 S Lemon Ave #4133 Walnut,
            CA 91789, USA.
          </p>
        </section>

        <section>
          <h2 className="text-xl font-semibold mb-2">
            3. Propriété intellectuelle
          </h2>
          <p>
            L&apos;ensemble du contenu du site (textes, graphismes, logos, etc.)
            est la propriété exclusive de Freelance CI SAS. Toute reproduction
            ou distribution sans autorisation est interdite.
          </p>
        </section>

        <section>
          <h2 className="text-xl font-semibold mb-2">
            4. Responsabilité
          </h2>
          <p>
            Freelance CI agit comme intermédiaire entre clients et freelances.
            Nous ne pouvons être tenus responsables des litiges entre les
            parties. Notre système de séquestre et de jalons offre une
            protection optimale.
          </p>
        </section>
      </div>
    </div>
  );
}
