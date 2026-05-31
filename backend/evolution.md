# Évolution du projet — Freelance CI API

> Suivi des fonctionnalités réalisées et à venir.

---

## Légende

- [x] Terminé
- [ ] À faire
- [ ] ~~Bloqué~~ / ~~Abandonné~~

---

## Phase 1 : Fondations

### Projet Laravel
- [x] Initialisation du projet Laravel 11 (API)
- [x] Configuration PostgreSQL / Supabase
- [x] Génération de la clé d'application
- [x] Structure des répertoires (Services, Contrôleurs, Middleware, Enums)

### Base de données
- [x] 22 fichiers de migration (40+ tables)
- [x] Types énumérés PostgreSQL (24 enums PHP)
- [x] Toutes les contraintes, clés étrangères, index
- [x] Triggers PostgreSQL (6 triggers : updated_at, rating, quotes_count, invoice, last_message, wallet)
- [x] Vues PostgreSQL (4 vues : freelance_listing, admin_dashboard, monthly_revenue, genius_pay_monitoring)
- [x] Données initiales (catégories, plans abonnement, settings plateforme)
- [x] Index de performance (87 indexes dont 2 GIN full-text)

### Modèles
- [x] 40 modèles Eloquent avec relations complètes
- [x] UUID comme clés primaires (`HasUuids` trait)
- [x] Soft Deletes sur User
- [x] Casts vers les enums PHP natifs

---

## Phase 2 : Authentification & Sécurité

- [x] Middleware de validation JWT Supabase (`SupabaseJwtMiddleware`)
- [x] Middleware RBAC par rôle (`CheckRole`)
- [x] Gestionnaire d'exceptions JSON
- [x] Inscription avec création automatique des profils (client/freelance)
- [x] Auth sociale (Google, LinkedIn, GitHub) — proxy vers Supabase
- [x] Validation des requêtes (`RegisterRequest`)

---

## Phase 3 : API — Modules fonctionnels

### Profils
- [x] Profil commun (GET/PUT)
- [x] Profil client (GET/PUT)
- [x] Profil freelance (GET/PUT)
- [x] Gestion des compétences (ajout/suppression)
- [x] Gestion du portfolio (ajout/suppression)
- [x] Liste publique des freelances (recherche full-text, filtres, tri)
- [x] Détail public d'un freelance

### Projets
- [x] CRUD projets (création, modification, suppression soft)
- [x] Gestion des fichiers joints
- [x] Liste publique (filtres : statut, catégorie, budget, recherche)
- [x] Incrémentation des vues

### Devis
- [x] CRUD devis
- [x] Vérification des limites d'abonnement (max proposals)
- [x] Acceptation → création contrat + escrow
- [x] Refus / Retrait

### Contrats & Jalons
- [x] Création automatique depuis devis accepté
- [x] Signature bilatérale (client + freelance)
- [x] Gestion des jalons (CRUD, livraison, validation)
- [x] CGU au moment de la signature

### Paiements & Genius Pay
- [x] Service d'intégration Genius Pay (création transaction, vérification statut)
- [x] Initiation de paiement
- [x] Webhook Genius Pay (signature HMAC vérifiée)
- [x] Synchronisation des transactions (cron)
- [x] Mode sandbox / production

### Escrow (Séquestre)
- [x] Mise en séquestre des fonds
- [x] Déblocage vers le freelance
- [x] Remboursement au client
- [x] Déblocage automatique différé (cron)

### Wallets
- [x] Porte-monnaie automatique à l'inscription
- [x] Crédit/débit des fonds
- [x] Historique des transactions
- [x] Demandes de retrait (Mobile Money, Virement)

### Messagerie
- [x] Conversations (par projet)
- [x] Envoi de messages avec statuts (envoyé, délivré, lu)
- [x] Fichiers joints dans les messages
- [x] Notifications à l'envoi

### Notifications
- [x] Création de notifications typées
- [x] Marquage lecture (individuel / tout)
- [x] Suppression
- [x] Support des notifications push et email (structure)

### Avis & Évaluation
- [x] Création d'avis avec critères (qualité, délai, communication)
- [x] Réponse aux avis
- [x] Recalcul automatique de la note moyenne (trigger)
- [x] Prévention des doublons

### Signalements & Litiges
- [x] Signalement utilisateur / projet
- [x] Workflow de modération (ouvert, en cours, résolu)
- [x] Litiges avec impact sur l'escrow
- [x] Résolution par admin

### Admin & Back-Office
- [x] Dashboard statistiques
- [x] Gestion des utilisateurs (statut)
- [x] Vérification des documents (approbation/rejet)
- [x] Gestion des signalements
- [x] Résolution des litiges
- [x] Monitoring des paiements (vue Genius Pay)
- [x] Paramètres plateforme (commission, boosts, maintenance)
- [x] Logs des actions admin

---

## Phase 4 : Tâches planifiées (Cron)

- [x] Synchronisation Genius Pay (`genius-pay:sync` → toutes les 10 min)
- [x] Déblocage automatique escrow (`escrow:release-auto` → toutes les heures)
- [x] Expiration des boosts (`boosts:expire` → quotidien)

---

## Phase 5 : Audit & Corrections de bugs

- [x] Correction du middleware auth (`auth:supabase` → `supabase.auth`)
- [x] Correction `ApiController::created()` manquante
- [x] Correction `AuthService` — `name` → `first_name`/`last_name`, retrait `supabase_id`
- [x] Correction `ContractService` — `signed_by_client_at` → `client_signed_at`, `amount` → `amount_xof`
- [x] Correction `ProfileService` — recherche full-text sur `first_name`/`last_name`, enums
- [x] Correction `AdminService` — retrait `Transaction`, `account_status` → `status`
- [x] Correction imports PHP invalides (`Model, HasUuids` → lignes séparées) dans 6 modèles
- [x] Ajout accesseur `name` sur `User` (composé de `first_name` + `last_name`)
- [x] Création `MilestoneStatus` enum (manquant)
- [x] Création `Transaction` model (référencé par `PaymentService`/`AdminService`)
- [x] 16 tests unitaires écrits (Auth, GeniusPay, Wallet, Contract, Category)

## Phase 6 : À venir (TODO)

### Fonctionnalités métier
- [ ] ~~Génération de factures PDF~~ (reporté à v2)
- [ ] Badge vérifié (achat annuel)
- [ ] Abonnements freelances (Starter, Pro, Expert)
- [ ] Boost de profil / projet
- [ ] Paiement des abonnements via Genius Pay
- [ ] Marketing / pages statiques (conditions, CGU, mentions légales)
- [ ] Parrainage / programme de referral

### Améliorations techniques
- [x] Tests unitaires (PHPUnit) — 16 tests, 0 échecs
- [ ] Tests fonctionnels sur PostgreSQL (besoin base de test dédiée)
- [ ] Documentation Swagger / OpenAPI (`darkaonline/l5-swagger`)
- [ ] Analyse statique (PHPStan level 6)
- [ ] Formatage automatique (Laravel Pint)
- [ ] Déploiement Docker
- [ ] CI/CD (GitHub Actions)
- [ ] Rate limiting Redis
- [ ] Cache Redis (cache-aside pour freelances/projets)
- [ ] WebSockets pour messagerie temps réel (Laravel Echo + Pusher/reverb)
- [ ] Recherche full-text avancée (Meilisearch / Algolia)
- [ ] Export CSV/Excel des données
- [ ] Internationalisation (i18n) — français par défaut

### Infrastructure
- [x] Projet Supabase Cloud créé et lié (`mtwfiovvhusawlxlvskj`, Frankfurt)
- [x] Configuration DB (PostgreSQL 17.6) — URL distante, JWT secret, clés API
- [x] Buckets Storage (6 : avatars, project-files, portfolio, message-files, verifications, invoices)
- [x] Providers OAuth configurés (Google, GitHub, LinkedIn)
- [ ] Mise en production (Laravel Forge / Vapor / Railway)
- [ ] Monitoring (Sentry, Laravel Pulse)
- [ ] Sauvegardes automatiques base de données

### Intégrations
- [ ] Envoi d'emails transactionnels (Mailgun / Postmark / Brevo)
- [ ] Notifications push (Firebase Cloud Messaging)
- [ ] Webhooks sortants (notifications externes)
- [ ] Module de chat temps réel (WebSocket)
- [ ] Paiement des freelances par transfert Genius Pay

---

## Suivi des versions

| Version | Date | Changements |
|---------|------|-------------|
| **v0.1.0** | Mai 2026 | Initialisation Laravel, migrations, modèles, enums |
| **v0.2.0** | Mai 2026 | Middleware auth Supabase, RBAC, base controllers |
| **v0.3.0** | Mai 2026 | Services métier (15 services), routes API (72 endpoints) |
| **v0.4.0** | Mai 2026 | Commandes cron, planification, finalisation API |
| **v0.5.0** | Mai 2026 | Supabase Cloud (DB/Auth/Storage lié), migrations seedées, OAuth configuré, buckets créés, modèles UUID corrigés (HasUuids trait), tests API GET /api/categories ✅ |
| **v0.6.0** | Mai 2026 | Audit & corrections (7 bugs majeurs), 16 tests unitaires, Swagger/OpenAPI |
| **v1.0.0** | — | Première release stable |
