import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "À propos | Freelance CI",
  description: "Découvrez Freelance CI, la plateforme de mise en relation entre clients et freelances en Côte d'Ivoire.",
};

export default function AboutPage() {
  return (
    <div className="container mx-auto px-4 py-16 max-w-3xl">
      <h1 className="text-3xl font-bold mb-6">À propos de Freelance CI</h1>

      <div className="prose prose-lg max-w-none">
        <p>
          Freelance CI est la première plateforme ivoirienne de mise en relation
          entre clients et freelances. Nous connectons les talents locaux avec
          les entreprises qui ont besoin de leurs services.
        </p>

        <h2>Notre mission</h2>
        <p>
          Faciliter l&apos;accès au travail indépendant en Côte d&apos;Ivoire en
          offrant une plateforme sécurisée, transparente et adaptée aux besoins
          du marché local.
        </p>

        <h2>Pourquoi Freelance CI ?</h2>
        <ul>
          <li>Paiements sécurisés via Genius Pay (Mobile Money, Cartes)</li>
          <li>Système de séquestre pour protéger clients et freelances</li>
          <li>Jalons et validation par étape</li>
          <li>Communauté de talents ivoiriens</li>
          <li>Support local en français</li>
        </ul>

        <h2>Notre équipe</h2>
        <p>
          Freelance CI est développé par une équipe passionnée basée à Abidjan,
          déterminée à révolutionner le marché du travail indépendant en Afrique
          francophone.
        </p>
      </div>
    </div>
  );
}
