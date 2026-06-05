import Link from "next/link";
import { Logo } from "@/components/shared/Logo";

export function Footer() {
  return (
    <footer className="bg-neutral text-neutral-content">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="text-center">
            <div className="flex justify-center">
              <Logo size="md" />
            </div>
            <p className="text-sm text-neutral-content/60 mt-3">
              La marketplace ivoirienne qui connecte freelances, clients et entrepreneurs. Paiements sécurisés via Mobile Money.
            </p>
          </div>
          <div className="text-center">
            <h6 className="font-semibold mb-3 text-sm uppercase tracking-wider text-neutral-content/80">Explorer</h6>
            <div className="flex flex-col gap-2 items-center">
              <Link href="/projects" className="text-sm text-neutral-content/60 hover:text-neutral-content transition-colors">Projets</Link>
              <Link href="/freelancers" className="text-sm text-neutral-content/60 hover:text-neutral-content transition-colors">Freelances</Link>
              <Link href="/categories" className="text-sm text-neutral-content/60 hover:text-neutral-content transition-colors">Catégories</Link>
            </div>
          </div>
          <div className="text-center">
            <h6 className="font-semibold mb-3 text-sm uppercase tracking-wider text-neutral-content/80">Légal</h6>
            <div className="flex flex-col gap-2 items-center">
              <Link href="/mentions-legales" className="text-sm text-neutral-content/60 hover:text-neutral-content transition-colors">Mentions légales</Link>
              <Link href="/confidentialite" className="text-sm text-neutral-content/60 hover:text-neutral-content transition-colors">Politique de confidentialité</Link>
              <Link href="/a-propos" className="text-sm text-neutral-content/60 hover:text-neutral-content transition-colors">À propos</Link>
              <Link href="/contact" className="text-sm text-neutral-content/60 hover:text-neutral-content transition-colors">Contact</Link>
            </div>
          </div>
          <div className="text-center">
            <h6 className="font-semibold mb-3 text-sm uppercase tracking-wider text-neutral-content/80">Paiements</h6>
            <p className="text-sm text-neutral-content/60 mb-3">Paiements sécurisés via</p>
            <div className="flex flex-wrap gap-2 justify-center">
              <span className="badge badge-soft badge-primary">Orange Money</span>
              <span className="badge badge-soft badge-primary">MTN MoMo</span>
              <span className="badge badge-soft badge-primary">Wave</span>
              <span className="badge badge-soft badge-primary">Carte bancaire</span>
            </div>
          </div>
        </div>
        <div className="mt-8 pt-6 border-t border-neutral/30 text-center">
          <p className="text-sm text-neutral-content/40">&copy; {new Date().getFullYear()} Freelance CI. Tous droits réservés.</p>
        </div>
      </div>
    </footer>
  );
}
