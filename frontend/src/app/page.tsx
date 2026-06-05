import Link from "next/link";

export default function Home() {
  return (
    <div>
      {/* Hero */}
      <section
        className="bg-neutral text-neutral-content"
        style={{
          backgroundImage: "url('/Freelance-CI_image_background.svg')",
          backgroundSize: "cover",
          backgroundPosition: "center",
        }}
      >
        <div className="container mx-auto px-4 py-20 md:py-28">
          <div className="max-w-3xl mx-auto text-center">
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
              Trouvez le freelance <span className="text-primary">idéal</span> en Côte d&apos;Ivoire
            </h1>
            <p className="text-lg md:text-xl text-neutral-content/70 mb-10 max-w-2xl mx-auto">
              La marketplace qui connecte les talents ivoiriens aux clients ambitieux.
              Paiements sécurisés, résultats garantis.
            </p>

            <div className="max-w-xl mx-auto">
              <form action="/projects" method="GET" className="join w-full shadow-2xl">
                <input
                  type="text"
                  name="search"
                  placeholder="Que cherchez-vous ? Développement, Design, Marketing..."
                  className="input input-bordered join-item w-full bg-base-100 text-base-content"
                />
                <button type="submit" className="btn btn-primary join-item px-6 font-semibold">
                  Rechercher
                </button>
              </form>
              <div className="flex flex-wrap justify-center gap-2 mt-4">
                <Link href="/projects?category=developpement" className="badge badge-soft badge-primary badge-sm hover:badge-secondary transition-colors">Développement</Link>
                <Link href="/projects?category=design" className="badge badge-soft badge-primary badge-sm hover:badge-secondary transition-colors">Design</Link>
                <Link href="/projects?category=marketing" className="badge badge-soft badge-primary badge-sm hover:badge-secondary transition-colors">Marketing</Link>
                <Link href="/projects?category=redaction" className="badge badge-soft badge-primary badge-sm hover:badge-secondary transition-colors">Rédaction</Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Stats */}
      <section className="bg-base-200 py-12">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            {[
              { value: "500+", label: "Freelances actifs" },
              { value: "1 200+", label: "Projets réalisés" },
              { value: "95%", label: "Clients satisfaits" },
              { value: "50M+", label: "FCFA en transactions" },
            ].map((stat) => (
              <div key={stat.label} className="text-center">
                <p className="text-3xl md:text-4xl font-bold text-primary">{stat.value}</p>
                <p className="text-sm text-base-content/60 mt-1">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* How it works */}
      <section className="py-16 md:py-20">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold mb-3">Comment ça marche ?</h2>
            <p className="text-base-content/60 max-w-xl mx-auto">En trois étapes simples, trouvez le freelance parfait pour votre projet</p>
          </div>
          <div className="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            {[
              {
                step: "01",
                title: "Publiez votre projet",
                desc: "Décrivez votre besoin, fixez votre budget et recevez des propositions de freelances qualifiés.",
              },
              {
                step: "02",
                title: "Choisissez un freelance",
                desc: "Comparez les profils, les portfolios et les avis pour sélectionner le talent idéal.",
              },
              {
                step: "03",
                title: "Payez en toute sécurité",
                desc: "Les fonds sont séquestrés et libérés étape par étape à la validation des jalons.",
              },
            ].map((item) => (
              <div key={item.step} className="text-center">
                <div className="w-14 h-14 rounded-full bg-brand-orange-light text-primary font-bold text-xl flex items-center justify-center mx-auto mb-4">{item.step}</div>
                <h3 className="font-bold text-lg mb-2">{item.title}</h3>
                <p className="text-sm text-base-content/60">{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Categories */}
      <section className="bg-base-200 py-16 md:py-20">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold mb-3">Explorez par catégorie</h2>
            <p className="text-base-content/60 max-w-xl mx-auto">Des talents ivoiriens dans tous les domaines</p>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            {[
              { name: "Développement Web", icon: "💻", count: "150 freelances" },
              { name: "Design Graphique", icon: "🎨", count: "120 freelances" },
              { name: "Marketing Digital", icon: "📈", count: "80 freelances" },
              { name: "Rédaction & Traduction", icon: "✍️", count: "60 freelances" },
              { name: "Vidéo & Animation", icon: "🎬", count: "45 freelances" },
              { name: "Musique & Audio", icon: "🎵", count: "30 freelances" },
              { name: "Photographie", icon: "📸", count: "35 freelances" },
              { name: "Conseil & Stratégie", icon: "💡", count: "50 freelances" },
            ].map((cat) => (
              <Link
                key={cat.name}
                href={`/projects?category=${cat.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[&\s]+/g, "-")}`}
                className="card bg-base-100 border border-base-300 hover:border-primary/30 hover:shadow-md transition-all"
              >
                <div className="card-body items-center text-center p-5">
                  <span className="text-3xl mb-2">{cat.icon}</span>
                  <h3 className="font-semibold text-sm">{cat.name}</h3>
                  <p className="text-xs text-base-content/50">{cat.count}</p>
                </div>
              </Link>
            ))}
          </div>
          <div className="text-center mt-8">
            <Link href="/categories" className="btn btn-outline btn-sm">Voir toutes les catégories</Link>
          </div>
        </div>
      </section>

      {/* For clients */}
      <section className="py-16 md:py-20">
        <div className="container mx-auto px-4">
          <div className="flex flex-col md:flex-row items-center gap-12 max-w-5xl mx-auto">
            <div className="flex-1">
              <div className="w-16 h-16 rounded-2xl bg-brand-green-light text-secondary flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <h2 className="text-3xl font-bold mb-4">Vous cherchez un freelance ?</h2>
              <p className="text-base-content/60 mb-6">Publiez votre projet gratuitement et recevez des offres de freelances ivoiriens qualifiés. Paiement sécurisé via escrow.</p>
              <ul className="space-y-3 mb-8">
                {["Publication gratuite de projet", "Paiement sécurisé par séquestre", "Jalons et validation par étape", "Avis et notes sur les freelances"].map((item) => (
                  <li key={item} className="flex items-center gap-2 text-sm">
                    <svg className="w-4 h-4 text-secondary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                    </svg>
                    {item}
                  </li>
                ))}
              </ul>
              <Link href="/auth/register?role=client" className="btn btn-primary">Publier un projet</Link>
            </div>
            <div className="flex-1 bg-base-200 rounded-2xl p-8 md:p-12 w-full">
              <div className="stats stats-vertical shadow-sm w-full">
                <div className="stat text-center">
                  <div className="stat-title">Projets publiés</div>
                  <div className="stat-value text-primary">1 200+</div>
                  <div className="stat-desc">+15% ce mois</div>
                </div>
                <div className="stat text-center">
                  <div className="stat-title">Taux de satisfaction</div>
                  <div className="stat-value text-secondary">95%</div>
                  <div className="stat-desc">Clients satisfaits</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* For freelancers */}
      <section className="bg-base-200 py-16 md:py-20">
        <div className="container mx-auto px-4">
          <div className="flex flex-col md:flex-row-reverse items-center gap-12 max-w-5xl mx-auto">
            <div className="flex-1">
              <div className="w-16 h-16 rounded-2xl bg-brand-orange-light text-primary flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <h2 className="text-3xl font-bold mb-4">Vous êtes freelance ?</h2>
              <p className="text-base-content/60 mb-6">Créez votre profil, présentez votre portfolio et trouvez des missions adaptées à vos compétences. Paiement sécurisé garanti.</p>
              <ul className="space-y-3 mb-8">
                {["Profil personnalisable avec portfolio", "Accès à des projets vérifiés", "Paiement sécurisé et à l'heure", "Boost de visibilité pour vos services"].map((item) => (
                  <li key={item} className="flex items-center gap-2 text-sm">
                    <svg className="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                    </svg>
                    {item}
                  </li>
                ))}
              </ul>
              <Link href="/auth/register?role=freelance" className="btn btn-primary">Créer mon profil</Link>
            </div>
            <div className="flex-1 bg-base-100 rounded-2xl p-8 md:p-12 w-full border border-base-300">
              <div className="text-center">
                <div className="text-4xl font-bold text-primary mb-2">10-15%</div>
                <p className="text-sm text-base-content/60 mb-4">Commission par transaction seulement</p>
                <div className="flex justify-center gap-4 text-sm">
                  <div className="text-center">
                    <div className="font-bold text-lg">5 000 F</div>
                    <div className="text-base-content/50">Abonnement/mois</div>
                  </div>
                  <div className="w-px bg-base-300" />
                  <div className="text-center">
                    <div className="font-bold text-lg">2 000 F</div>
                    <div className="text-base-content/50">Boost visibilité</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="bg-neutral text-neutral-content py-16">
        <div className="container mx-auto px-4 text-center max-w-2xl">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Prêt à commencer ?</h2>
          <p className="text-neutral-content/70 mb-8">Rejoignez la plus grande communauté de freelances et clients en Côte d&apos;Ivoire</p>
          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            <Link href="/auth/register?role=client" className="btn btn-primary btn-lg">Je cherche un freelance</Link>
            <Link href="/auth/register?role=freelance" className="btn btn-outline btn-lg text-neutral-content border-neutral-content/30 hover:bg-neutral-content hover:text-neutral">Je propose mes services</Link>
          </div>
        </div>
      </section>
    </div>
  );
}
