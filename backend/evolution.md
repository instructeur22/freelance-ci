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
- [x] **22 tests** (21 verts, 1 skip)

## Phase 6 : Audit complet & corrections masse — Juin 2026

### P0 — Runtime fixes
- [x] **QuoteController ↔ QuoteService** : Ajout de 7 méthodes manquantes (`listForProject`, `find`, `update`, `delete`, `create`, `accept`, `refuse`) — plantait au runtime
- [x] **EscrowService::autoReleaseSchedule()** : retour `void` → `int` (command utilisait `$count`)
- [x] **GeniusPayService::syncTransactions()** : retour `void` → `array{checked, updated, failed}` (command utilisait clés)

### P1 — Modèles alignés sur migrations (28 fichiers)
- [x] **6 modèles critiques refaits** : Invoice, VerifiedBadge, PortfolioItem, SubscriptionPlanConfig, FreelanceSubscription, AuthToken
- [x] **4 SoftDeletes ajoutés** : Contract, Quote, Review, Message
- [x] **4 relations manquantes** : Payment.transaction(), WithdrawalRequest.wallet(), PaymentSyncLog.payment(), FreelanceLanguage.freelanceProfile()
- [x] **HasUuids ajouté à Boost**
- [x] **18 modèles secondaires corrigés** : User, FreelanceProfile, Skill, FreelanceSkill, FreelanceLanguage, Verification, Report, Dispute, PortfolioFile, ProjectFile, MessageFile, AdminLog, SocialAccount, PlatformSetting, JobCategory, Review, Boost, PaymentSyncLog
- [x] **ReviewService::updateTargetRating()** : persiste maintenant `average_rating` + `total_reviews` dans FreelanceProfile

### P2 — Sécurité & tests
- [x] `.env.testing` nettoyé (SQLite mémoire, plus de prod Supabase)
- [x] `phpunit.pgsql.xml` nettoyé (credentials `CHANGE_ME`)
- [x] Swagger regénéré (62 chemins, 72 opérations)

## Phase 7 : Consolidation schéma unifié — Juin 2026

### Migrations (35 fichiers `2026_06_02_*`)
- [x] **Phase A** — Users (`phone_verified_at`, CHECK contraintes), AuthTokens (`token_hash`, `type`, `used_at`), SocialAccounts (`access_token`, `expires_at`)
- [x] **Phase B** — Profiles (`display_name`), ClientProfiles (`company_sector`, UNIQUE user_id), FreelanceProfiles (`tagline`, `daily_rate_xof`, `availability_note`, `response_rate`, UNIQUE user_id), FreelanceSkills/FreelanceLanguages (rename `freelance_profile_id` → `freelance_id`), Skills (`is_active`), JobCategories (`icon_url`), + UNIQUE `(freelance_id, language)`
- [x] **Phase C** — Projects (`featured_until`, `selected_quote_id`, `required_skills` JSON→JSONB), Quotes (`cover_letter`, `accepted_at`, `refused_at`, `withdrawn_at`, UNIQUE project+freelance), Contracts (`commission_rate`, `commission_xof`, `completed_at`, `cancelled_at`, `terms_text`, `quote_id` NOT NULL), Milestones (`is_completed`)
- [x] **Phase D (critique)** — Payments (`payer_id`, `payee_id`, `genius_pay_transaction_id`, `genius_pay_status`, `gross_amount_xof`, `commission_xof`, timestamps cycle de vie, `provider_response`), GeniusPayWebhooks (`transaction_id`, `raw_payload`), PaymentSyncLog (recréée, ancienne → `payment_action_log`), Escrows (`release_requested_at`, `dispute_id`, UNIQUE contract_id), Wallets (`available_xof`, `pending_xof`, `total_earned_xof`, `total_withdrawn_xof`, UNIQUE user_id), WalletTransactions (`payment_id`, `direction`, `amount_xof`, `balance_before/after_xof`), Invoices (`issued_to_id`, `pdf_url`, `tax_xof`, `total_xof`), WithdrawalRequests (`genius_pay_transfer_id`, `bank_account`, `phone_number`, `withdrawal_method`, CHECK)
- [x] **Phase E** — Conversations (`client_id`, `freelance_id`, UNIQUE project+client+freelance), Messages (`delivered_at`), Notifications (`sent_email`, `sent_push`)
- [x] **Phase F** — Reviews (`rating_quality`, `rating_delay`, `rating_communication`, `is_public`, UNIQUE contract_id), ReviewReplies (UNIQUE review_id), Reports (`reported_project_id`), Boosts (`user_id`, `payment_id`, `target`), VerifiedBadges (`verification_id`, UNIQUE freelance_id)
- [x] **Phase G** — PlatformSettings (`updated_by`), AdminLogs (`target_table`), + Indexes (87 indexes dont GIN full-text), Triggers PostgreSQL (credit_wallet_on_escrow_release), Vues SQL (v_freelance_listing, v_admin_dashboard, v_monthly_revenue, v_genius_pay_monitoring, v_wallet_summary)
- [x] **Seeders** — Platform settings (commission, boosts, referral, etc.), Subscription plans (starter/pro/expert), Job categories (10), Skills (~60)

### Alignement code applicatif (28 fichiers)
- [x] **Config** : `geniuspay.php` (defaults pour éviter crash en test)
- [x] **Services** : `EscrowService` (balance_before/after_xof, released/refunded_amount), `PaymentService` (transaction_id dans return, confirm→find Payment), `ReviewService` (crée ReviewReply), `QuoteService` (enum→value array keys)
- [x] **Modèles** : `SocialAccount` (fillable + casts), `AuthToken` (fillable + casts), `FreelanceProfile` (boosts()), `Dispute` (openedBy alias)
- [x] **Tests** : 25 fichiers de test corrigés (8 feature, 17 unit). 169 tests, 347 assertions — tout vert.

### Ce qui a été délibérément différé
- [ ] ~~PK freelance_profiles/client_profiles → user_id~~ (UNIQUE user_id déjà présent)
- [ ] ~~PK composite freelance_skills/freelance_languages~~ (Eloquent ne supporte pas PK composites, UNIQUE déjà présent)
- [ ] ~~PK wallets → user_id~~ (UNIQUE user_id déjà présent)
- [ ] ~~Renommage hourly_rate_min/max → _xof~~ (String→Decimal = migration de données risquée)

## Phase 8 : À venir (TODO)

### Tests
- [ ] Remplacer `test_that_true_is_true` par des tests réels
- [ ] Ajouter factories pour les 40 modèles
- [ ] Feature tests pour les 11 contrôleurs non couverts
- [ ] Tests unitaires significatifs pour les 9 services non testés

### Fonctionnalités métier
- [ ] ~~Génération de factures PDF~~ (reporté à v2)
- [x] Badge vérifié (achat annuel via Genius Pay, endpoints API + admin)
- [x] Abonnements freelances (Starter, Pro, Expert) — achat via Genius Pay, upgrade, cancel, endpoints API
- [x] Boost de profil / projet (achat via Genius Pay, endpoints API + admin)
- [x] Paiement des abonnements via Genius Pay
- [x] Marketing / pages statiques (conditions, CGU, mentions légales — About, Terms, Privacy, Contact)
- [x] Parrainage / programme de referral (code unique, tracking, récompense au premier paiement du filleul)

### Améliorations techniques
- [x] Swagger / OpenAPI (62 chemins documentés)
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
| **v0.3.0** | Mai 2026 | Services métier (14 services), routes API (72 endpoints) |
| **v0.4.0** | Mai 2026 | Commandes cron, planification, finalisation API |
| **v0.5.0** | Mai 2026 | Supabase Cloud lié, migrations seedées, OAuth + buckets configurés, HasUuids trait, tests API ✅ |
| **v0.6.0** | Mai 2026 | Audit & corrections (7 bugs majeurs), 16 tests unitaires, Swagger/OpenAPI |
| **v0.7.0** | Juin 2026 | **Audit complet** — 3 P0 runtime fixes, 28 modèles alignés migrations, SoftDeletes, relations manquantes, sécurité credentials, 21/22 tests verts |
| **v0.8.0** | Juin 2026 | **Fonctionnalités métier** — Badge vérifié, Boost, Abonnements, Marketing pages, Parrainage — 5 endpoints Swagger, 18 nouveaux endpoints API |
| **v1.0.0** | Juin 2026 | **Consolidation schéma unifié** — 35 migrations `2026_06_02_*` (Phases A→G complètes), alignement de 28 fichiers modèle/service/contrôleur/test. 169 tests, 347 assertions — tout vert. |
