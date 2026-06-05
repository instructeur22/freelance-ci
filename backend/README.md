# Freelance CI — API Backend

> **Freelance CI** est une plateforme de freelancing ivoirienne qui connecte freelances, clients et entrepreneurs. Ce dépôt contient l'API backend complète.

---

## Table des matières

- [Présentation du projet](#présentation-du-projet)
- [Choix techniques](#choix-techniques)
- [Architecture](#architecture)
- [Schéma de la base de données](#schéma-de-la-base-de-données)
- [Documentation de l'API](#documentation-de-lapi)
- [Tests](#tests)
- [Installation & Démarrage](#installation--démarrage)
- [Structure du projet](#structure-du-projet)
- [Déploiement](#déploiement)
- [Contribuer](#contribuer)

---

## Présentation du projet

### Problème

En Côte d'Ivoire, les freelances et les clients peinent à se connecter efficacement. Les plateformes internationales (Upwork, Fiverr, Malt) sont peu adaptées au marché local :
- moyens de paiement limités (Mobile Money — Orange Money, MTN MoMo, Wave)
- barrière de la langue
- absence de support local
- frais de transaction élevés

### Solution

Freelance CI propose une plateforme tout-en-un avec :

- Création de profils professionnels (client & freelance)
- Publication et gestion de projets
- Système de devis/propositions
- Contrats et jalons (milestones)
- Paiements sécurisés via **Genius Pay** (Mobile Money, Carte, Virement)
- Système de **séquestre (escrow)** pour protéger les deux parties
- Messagerie intégrée
- Notifications en temps réel
- Avis et évaluations après projet
- Partage de fichiers
- Boost de profil/projet
- Badge vérifié
- Abonnements freelances (Starter, Pro, Expert)
- Administration & modération

---

## Choix techniques

### Stack

| Technologie | Version | Rôle |
|-------------|---------|------|
| **PHP** | 8.4 | Langage backend |
| **Laravel** | 13.12 (Laravel 11) | Framework API |
| **PostgreSQL** | 17 (Supabase Cloud) | Base de données |
| **Supabase** | — | Auth, Storage, DB managée |
| **Genius Pay** | — | Agrégateur de paiements |
| **Firebase PHP-JWT** | ^7.0 | Validation des JWT Supabase via JWKS |
| **Laravel File Cache** | — | Cache des clés JWKS (raw JSON, pas d'objets sérialisés) |

### Pourquoi ces choix ?

| Choix | Justification |
|-------|---------------|
| **Laravel** | Framework PHP le plus populaire, écosystème riche (ORM Eloquent, migrations, files d'attente, notification, events). Architecture MVC claire et testable. |
| **Supabase Auth** | Authentification sociale prête à l'emploi (Google, LinkedIn, GitHub). JWT sécurisés. Pas besoin de gérer les mots de passe côté serveur. |
| **Supabase Storage** | Upload de fichiers avec URLs présignées. Buckets sécurisés pour avatars, documents, portfolio, factures. |
| **PostgreSQL** | Base de données relationnelle robuste. Support natif des UUID, JSONB, Full-Text Search, transactions, triggers. |
| **Genius Pay** | Agrégateur de paiements adapté au marché ivoirien : Orange Money, MTN MoMo, Wave, cartes bancaires, USSD. |
| **PHP 8.4** | Enums natifs, typed properties, readonly classes, performant. |
| **JWT (sans Sanctum)** | Puisque Supabase gère l'auth, Laravel valide simplement les tokens JWT émis par Supabase via leur JWKS (clés publiques). Pas besoin de Sanctum. |
| **catch (\Throwable)** | Les erreurs PHP (TypeError) sont catchées au même titre que les Exceptions, évitant les 500 silencieux. |

### Architecture des décisions (ADR)

1. **UUID comme clés primaires** — Sécurité accrues (pas d'ID séquentiels), compatibilité avec Supabase, permet la fusion de données.
2. **Soft Deletes** — Les utilisateurs ne sont jamais vraiment supprimés, seulement masqués (`deleted_at`).
3. **Service Layer** — Toute la logique métier est dans `app/Services/`, les contrôleurs sont des adaptateurs HTTP légers.
4. **Enums PHP natifs** — Tous les statuts sont des enums PHP typés (`UserRole`, `ProjectStatus`, `PaymentStatus`, etc.).
5. **Paiement via Genius Pay uniquement** — Un seul agrégateur pour simplifier. Plusieurs canaux supportés (Mobile Money, Carte, Virement).

---

## Architecture

### Diagramme d'architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   CLIENT (Next.js)                            │
│  - Interface utilisateur                                      │
│  - Supabase JS SDK (auth, storage)                            │
└────────────────────────┬────────────────────────────────────┘
                         │ HTTPS / JSON API
                         │ JWT Bearer Token (Supabase)
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              LARAVEL API — Backend PHP                         │
│                                                               │
│  ┌─────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────┐  │
│  │ Routes  │  │Middleware│  │Contrôl.  │  │   Services    │  │
│  │ /api/v1 │  │· JWT     │  │(13)      │  │(14 services)  │  │
│  │  72     │  │· RBAC    │  │          │  │               │  │
│  └─────────┘  └──────────┘  └────┬─────┘  └──────┬────────┘  │
│                                   │               │            │
│                                   ▼               ▼            │
│                          ┌─────────────────────────────┐      │
│                          │       Eloquent Models        │      │
│                          │        (40 modèles)           │      │
│                          └─────────────┬───────────────┘      │
└────────────────────────────────────────┼──────────────────────┘
                                         │
              ┌──────────────────────────┼──────────────────────┐
              │                          │                      │
              ▼                          ▼                      ▼
   ┌──────────────────┐   ┌──────────────────┐   ┌──────────────────┐
   │   SUPABASE DB    │   │ SUPABASE AUTH    │   │SUPABASE STORAGE  │
   │   PostgreSQL     │   │ Google, LinkedIn │   │ Avatars, fichiers │
   │   40+ tables     │   │ GitHub, Email    │   │ Portfolio, docs   │
   └──────────────────┘   └──────────────────┘   └──────────────────┘
              │
              ▼
   ┌──────────────────┐
   │  GENIUS PAY      │
   │  Orange Money    │
   │  MTN MoMo, Wave │
   │  Cartes, USSD    │
   └──────────────────┘
```

### Flux de données typique

```
1. Client publie un projet        → POST /api/projects
2. Freelance soumet un devis      → POST /api/projects/{id}/quotes
3. Client accepte le devis        → POST /api/quotes/{id}/accept
   → Contrat créé automatiquement
   → Séquestre (escrow) initialisé
4. Client initie le paiement      → POST /api/payments/initiate
   → Redirection vers Genius Pay
5. Genius Pay notifie (webhook)   → POST /api/webhooks/genius-pay
6. Freelance livre le travail     → POST /api/contracts/{id}/milestones/{m}/deliver
7. Client valide la livraison     → POST /api/contracts/{id}/milestones/{m}/validate
   → Fonds débloqués du séquestre
   → Wallet du freelance crédité
8. Avis réciproque                → POST /api/contracts/{contract}/review
```

---

## Schéma de la base de données

### Structure (40+ tables)

```
users
├── social_accounts        (Google, LinkedIn, GitHub)
├── auth_tokens            (reset password, email verify, refresh)
├── profiles               (nom, avatar, bio, localisation)
├── client_profiles        (entreprise, secteur, SIRET)
├── freelance_profiles     (taux, disponibilité, notation)
├── freelance_skills       (compétences)
├── freelance_languages    (langues parlées)
├── portfolio_items        (projets réalisés)
├── portfolio_files        (médias du portfolio)
├── verifications          (documents vérifiés)
├── freelance_subscriptions (abonnements)
├── wallets                (porte-monnaie)
├── wallet_transactions    (historique transactions)
├── withdrawal_requests    (demandes de retrait)
├── notifications          (notifications)
├── reviews_given          (avis donnés)
├── reviews_received       (avis reçus)
├── boosts                 (profil/projet boosté)
├── verified_badges        (badge vérifié)

projects                  (annonces de projets)
├── project_files          (fichiers joints)
├── quotes                 (devis freelances)
├── contracts              (contrats signés)
│   ├── milestones         (jalons)
│   ├── escrows            (séquestre)
│   ├── reviews            (avis)
│   └── disputes           (litiges)
├── conversations          (messagerie)
│   └── messages
│       └── message_files

payments                  (transactions)
├── invoices               (factures)
├── genius_pay_webhooks    (logs webhooks)
├── payment_sync_log       (logs synchro)

reports                   (signalements)
disputes                  (litiges)

job_categories            (catégories métier)
└── skills                 (compétences)

subscription_plans_config (configuration abonnements)
platform_settings         (paramètres globaux)
admin_logs                (logs d'actions admin)
```

### Vues PostgreSQL

| Vue | Description |
|-----|-------------|
| `v_freelance_listing` | Liste enrichie des freelances (nom, note, tarif, boost) |
| `v_admin_dashboard` | Statistiques du tableau de bord admin |
| `v_monthly_revenue` | Revenus mensuels par type de transaction |
| `v_genius_pay_monitoring` | Monitoring des transactions Genius Pay |

### Triggers PostgreSQL

| Trigger | Action |
|---------|--------|
| `set_updated_at` | Met à jour `updated_at` automatiquement |
| `update_freelance_rating` | Recalcule la note moyenne après un avis |
| `update_project_quotes_count` | Met à jour le compteur de devis d'un projet |
| `generate_invoice_number` | Génère un numéro de facture séquentiel |
| `update_conversation_last_message` | Met à jour la date du dernier message |
| `update_wallet_on_payment_released` | Crédite le wallet lors d'un déblocage de fonds |

---

## Documentation de l'API

### Authentification

L'API utilise les **JWT Supabase** pour l'authentification.

```
Authorization: Bearer <supabase-access-token>
```

Le middleware `SupabaseJwtMiddleware` décode et valide le token JWT via les **JWKS** (JSON Web Key Sets) de Supabase.
Les clés publiques sont récupérées depuis `https://<project>.supabase.co/auth/v1/.well-known/jwks.json`
et stockées en cache fichier (raw JSON, pas d'objets sérialisés, pour éviter les `__PHP_Incomplete_Class`).
En environnement `local`, la vérification SSL est désactivée (`Http::withoutVerifying()`).

### En-têtes communs

| En-tête | Valeur |
|---------|--------|
| `Authorization` | `Bearer <token>` (requis pour routes auth) |
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

### Réponses standard

**Succès :**
```json
{
  "message": "Success",
  "data": { ... }
}
```

**Paginated :**
```json
{
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

**Erreur :**
```json
{
  "message": "Description de l'erreur"
}
```

**Erreur validation (422) :**
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["L'email est requis"]
  }
}
```

### Routes détaillées

<details>
<summary><strong>🔓 Routes publiques</strong></summary>

#### Catégories & Compétences

```
GET /api/categories
```
Liste toutes les catégories métier avec leurs sous-catégories et compétences.

```
GET /api/categories/{id}/skills
```
Liste les compétences d'une catégorie.

#### Liste des freelances

```
GET /api/freelances
```
Liste paginée des freelances disponibles. Filtres :
- `search` — recherche full-text (nom, bio, tagline)
- `category` — filtre par catégorie (slug)
- `rate_min` / `rate_max` — filtre par taux journalier (FCFA)
- `skills` — compétences requises (IDs séparés par virgule)
- `sort` — tri (`rating`, `price_asc`, `price_desc`, `missions`, `newest`)
- `page` / `per_page` — pagination

```
GET /api/freelances/{id}
```
Détail d'un freelance (profil, compétences, portfolio, avis).

#### Projets publics

```
GET /api/projects
```
Liste paginée des projets ouverts.
Filtres : `status`, `category`, `search`, `budget_min`, `budget_max`, `sort`

```
GET /api/projects/{id}
```
Détail d'un projet.

#### Auth

```
POST /api/auth/register
```
Inscription. Body : `{ email, password, role, first_name, last_name, phone? }`

```
POST /api/auth/social/{provider}
```
Auth sociale. `provider` = `google`, `linkedin`, `github`.

#### Webhook

```
POST /api/webhooks/genius-pay
```
Webhook Genius Pay (notification de statut de transaction).
</details>

<details>
<summary><strong>🔐 Routes authentifiées (JWT requis)</strong></summary>

#### Auth

```
GET /api/auth/me           → Profil de l'utilisateur connecté
POST /api/auth/login       → Validation du token Supabase
```

#### Profils

```
GET    /api/profiles/me                          → Profil complet
PUT    /api/profiles/me                          → Modifier profil commun
GET    /api/profiles/client                      → Profil client
PUT    /api/profiles/client                      → Modifier profil client
GET    /api/profiles/freelance                   → Profil freelance
PUT    /api/profiles/freelance                   → Modifier profil freelance
POST   /api/profiles/freelance/skills            → Ajouter compétence (body : { skill_id, level })
DELETE /api/profiles/freelance/skills/{skill}    → Retirer compétence
POST   /api/profiles/freelance/portfolio         → Ajouter projet portfolio
DELETE /api/profiles/freelance/portfolio/{item}  → Supprimer projet portfolio
```

#### Projets

```
POST   /api/projects                             → Créer un projet (client)
PUT    /api/projects/{id}                        → Modifier son projet
DELETE /api/projects/{id}                        → Supprimer son projet (soft delete)
POST   /api/projects/{id}/files                  → Ajouter un fichier au projet
DELETE /api/projects/{project}/files/{file}      → Supprimer un fichier
```

#### Devis

```
GET    /api/projects/{project}/quotes            → Voir les devis d'un projet
POST   /api/projects/{project}/quotes            → Proposer un devis (freelance)
GET    /api/quotes/{id}                          → Détail d'un devis
PUT    /api/quotes/{id}                          → Modifier son devis
DELETE /api/quotes/{id}                          → Retirer son devis
POST   /api/quotes/{id}/accept                   → Accepter un devis (client) → contrat + escrow
POST   /api/quotes/{id}/refuse                   → Refuser un devis (client)
```

#### Contrats & Jalons

```
GET    /api/contracts                            → Liste des contrats
GET    /api/contracts/{id}                       → Détail d'un contrat
POST   /api/contracts/{id}/sign                  → Signer le contrat
POST   /api/contracts/{id}/milestones            → Ajouter un jalon
PUT    /api/contracts/{contract}/milestones/{m}  → Modifier un jalon
POST   /api/contracts/{contract}/milestones/{m}/deliver  → Marquer livré
POST   /api/contracts/{contract}/milestones/{m}/validate → Marquer validé
```

#### Paiements & Wallet

```
POST   /api/payments/initiate                    → Initier paiement (body : { contract_id, ... })
POST   /api/payments/{id}/confirm                → Confirmer paiement
GET    /api/payments/{id}                        → Statut paiement
GET    /api/payments                             → Historique des paiements
GET    /api/wallet                               → Solde du wallet
GET    /api/wallet/transactions                  → Transactions du wallet
POST   /api/wallet/withdraw                      → Demander un retrait
```

#### Messagerie

```
GET    /api/conversations                        → Liste des conversations
POST   /api/conversations                        → Démarrer une conversation
GET    /api/conversations/{id}                   → Messages d'une conversation
POST   /api/conversations/{id}/messages          → Envoyer un message
PUT    /api/messages/{id}/read                   → Marquer un message comme lu
```

#### Notifications

```
GET    /api/notifications                        → Liste des notifications
PUT    /api/notifications/{id}/read              → Marquer une notification comme lue
PUT    /api/notifications/read-all               → Tout marquer comme lu
DELETE /api/notifications/{id}                   → Supprimer une notification
```

#### Avis

```
POST   /api/contracts/{contract}/review          → Laisser un avis
GET    /api/freelances/{freelance}/reviews       → Avis d'un freelance
POST   /api/reviews/{review}/reply               → Répondre à un avis
```
</details>

<details>
<summary><strong>🛡️ Routes Admin (JWT + rôle admin)</strong></summary>

```
GET    /api/admin/dashboard                       → Statistiques
GET    /api/admin/users                           → Liste des utilisateurs
PUT    /api/admin/users/{id}/status               → Modifier statut (suspendre/bannir)
GET    /api/admin/verifications                   → Vérifications en attente
POST   /api/admin/verifications/{id}/approve      → Approuver une vérification
POST   /api/admin/verifications/{id}/reject       → Rejeter une vérification
GET    /api/admin/reports                         → Signalements
PUT    /api/admin/reports/{id}                    → Résoudre un signalement
GET    /api/admin/disputes                        → Litiges
PUT    /api/admin/disputes/{id}                   → Résoudre un litige
GET    /api/admin/payments                        → Monitoring paiements
GET    /api/admin/settings                        → Paramètres plateforme
PUT    /api/admin/settings/{key}                  → Modifier paramètre
```
</details>

### Documentation interactive (Swagger)

L'API est documentée avec OpenAPI 3.0 via `darkaonline/l5-swagger`.

```bash
# Générer la documentation
php artisan l5-swagger:generate

# Accéder à l'interface Swagger UI
# http://localhost:8000/api/documentation
```

Le fichier de spécification est généré dans `storage/api-docs/api-docs.json`.

### Codes HTTP utilisés

| Code | Usage |
|------|-------|
| 200 | Succès (GET, PUT, PATCH) |
| 201 | Créé (POST) |
| 204 | Pas de contenu (DELETE) |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Non autorisé (rôle insuffisant) |
| 404 | Ressource introuvable |
| 422 | Erreur de validation |
| 429 | Trop de requêtes (rate limit) |
| 500 | Erreur interne |

---

## Installation & Démarrage

### Prérequis

- [PHP](https://php.net) 8.4 avec extensions : `pdo_pgsql`, `mbstring`, `xml`, `curl`, `gd`
- [Composer](https://getcomposer.org) ≥ 2.x
- [PostgreSQL](https://postgresql.org) 17 (via Supabase Cloud)
- [Node.js](https://nodejs.org) (optionnel, pour les assets)

### Installation

```bash
# 1. Cloner le projet
git clone <url-du-repo> freelance-ci-api
cd freelance-ci-api

# 2. Installer les dépendances PHP
composer install

# 3. Copier et configurer l'environnement
cp .env.example .env
php artisan key:generate
```

### Configuration

Configurez votre fichier `.env` :

```env
# Environnement
APP_ENV=local              # local → Http::withoutVerifying() pour JWKS
APP_DEBUG=false
APP_URL=http://localhost:8000

# Base de données (Supabase PostgreSQL 17.6, Frankfurt)
DB_CONNECTION=pgsql
DB_HOST=db.mtwfiovvhusawlxlvskj.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<mot-de-passe>
DB_SSLMODE=require

# Supabase Auth (projet mtwfiovvhusawlxlvskj)
SUPABASE_URL=https://mtwfiovvhusawlxlvskj.supabase.co
SUPABASE_ANON_KEY=<clé-anonyme>
SUPABASE_SERVICE_KEY=<clé-service>
SUPABASE_JWT_SECRET=<secret-jwt-supabase>

# Logs
LOG_STACK=daily

# Session
SESSION_SECURE_COOKIE=true

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000

# Genius Pay
GENIUS_PAY_API_KEY=<clé-api>
GENIUS_PAY_SITE_ID=<site-id>
GENIUS_PAY_WEBHOOK_SECRET=<secret-webhook>
GENIUS_PAY_MODE=test
```

### Base de données (Supabase Cloud)

```bash
# Lier le projet Supabase existant
supabase login
supabase init
supabase link --project-ref mtwfiovvhusawlxlvskj

# Lancer les migrations (sur le cloud)
php artisan migrate

# Insérer les données initiales
php artisan db:seed
```

### Configuration Supabase (Dashboard)

1. **Auth providers** : Activer Google, GitHub, LinkedIn avec les credentials OAuth
2. **CORS** : Ajouter `http://localhost:3000`, `http://localhost:8000` dans Project Settings → API → Config
3. **Storage buckets** (créés automatiquement par migration) : `avatars`, `project-files`, `portfolio`, `message-files`, `verifications`, `invoices`

### Lancer le serveur

```bash
# Démarrer le serveur de développement
php artisan serve

# Dans un terminal séparé : lancer la file d'attente
php artisan queue:work

# Planifier les tâches cron (sur le serveur)
php artisan schedule:work
```

### Commandes disponibles

```bash
php artisan genius-pay:sync      # Synchroniser transactions Genius Pay
php artisan escrow:release-auto  # Débloquer les fonds séquestre automatiquement
php artisan boosts:expire        # Expirer les boosts terminés
```

---

## Structure du projet

```
backend/
├── app/
│   ├── Console/
│   │   └── Commands/              # Commandes Artisan (cron)
│   │       ├── ExpireBoosts.php
│   │       ├── ReleaseEscrowFunds.php
│   │       └── SyncGeniusPayTransactions.php
│   ├── Enums/                     # 24 énumérations PHP
│   │   ├── UserRole.php
│   │   ├── ProjectStatus.php
│   │   ├── PaymentStatus.php
│   │   ├── ContractStatus.php
│   │   └── ...
│   ├── Exceptions/
│   │   └── Handler.php            # Gestionnaire d'erreurs JSON
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/            # 13 contrôleurs REST
│   │   │       ├── AuthController.php
│   │   │       ├── ProfileController.php
│   │   │       ├── ProjectController.php
│   │   │       ├── QuoteController.php
│   │   │       ├── ContractController.php
│   │   │       ├── PaymentController.php
│   │   │       ├── WalletController.php
│   │   │       ├── MessageController.php
│   │   │       ├── NotificationController.php
│   │   │       ├── ReviewController.php
│   │   │       ├── AdminController.php
│   │   │       └── CategoryController.php
│   │   ├── Middleware/
│   │   │   ├── SupabaseJwtMiddleware.php   # Validation JWT Supabase via JWKS
│   │   │   └── CheckRole.php               # RBAC par rôle
│   │   └── Requests/
│   │       └── Auth/
│   │           └── RegisterRequest.php
│   ├── Models/                    # 40 modèles Eloquent
│   │   ├── User.php
│   │   ├── Profile.php
│   │   ├── Project.php
│   │   ├── Contract.php
│   │   ├── Payment.php
│   │   └── ...
│   └── Services/                  # 14 services métier
│       ├── AuthService.php
│       ├── ProfileService.php
│       ├── ProjectService.php
│       ├── QuoteService.php
│       ├── ContractService.php
│       ├── PaymentService.php
│       ├── GeniusPayService.php
│       ├── EscrowService.php
│       ├── WalletService.php
│       ├── MessageService.php
│       ├── NotificationService.php
│       ├── ReviewService.php
│       ├── AdminService.php
│       └── CategoryService.php
├── bootstrap/
│   └── app.php                    # Configuration Laravel
├── config/
│   ├── supabase.php               # Configuration Supabase
│   ├── geniuspay.php              # Configuration Genius Pay
│   └── database.php               # Connexions base de données
├── database/
│   └── migrations/                # 60 migrations (40+ tables)
├── resources/
│   └── views/vendor/l5-swagger/   # Interface Swagger UI
├── routes/
│   ├── api.php                    # 72 routes API REST
│   └── console.php                # Planification des tâches cron
├── storage/
│   └── api-docs/                  # Documentation OpenAPI générée
└── tests/                         # 169 tests PHPUnit (tout vert)
```

---

## Tests

### Tests

```bash
php artisan test
```

**169 tests, 347 assertions — tout vert.**

```bash
php artisan test
```

Couverture :
- **Unit/Services** (90 tests) — PaymentService, EscrowService, AuthService, GeniusPayService,
  ReviewService, ProfileService, ContractService, QuoteService, SubscriptionService,
  BoostService, BadgeService, WalletService, MessageService, NotificationService,
  ReferralService, CategoryService, AdminService
- **Feature/Api** (75 tests) — Auth, Profile, Project, Payment, Subscription, Boost, Badge,
  Admin, Review, Referral, Wallet, Message, Notification
- **Feature/Web** (4 tests) — Pages statiques (About, Terms, Privacy, Contact)

Base de test : SQLite `:memory` (aucune config PostgreSQL requise).

Base de production : PostgreSQL 17 (Supabase Cloud) — les triggers, vues et contraintes PostgreSQL
sont automatiquement ignorés par les migrations en environnement SQLite.

---

## Déploiement

### Docker

```dockerfile
FROM php:8.4-fpm-alpine

RUN docker-php-ext-install pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /var/www/html
WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
```

### Avec Laravel Forge / Vapor

1. Connecter le dépôt à Laravel Forge
2. Configurer les variables d'environnement (Supabase, Genius Pay)
3. Activer le scheduler
4. Configurer le worker de queue

### Supabase

1. Créer un projet Supabase
2. Copier l'URL et les clés (anon, service_role, JWT secret)
3. Lancer les migrations
4. Configurer les providers d'auth (Google, LinkedIn, GitHub)
5. Créer les buckets Storage : `avatars`, `project-files`, `portfolio`, `message-files`, `verifications`, `invoices`

---

## Contribuer

1. Créer une branche (`git checkout -b feature/ma-fonctionnalite`)
2. Commiter les changements (`git commit -m 'feat: ajout de...'`)
3. Pusher (`git push origin feature/ma-fonctionnalite`)
4. Ouvrir une Pull Request

### Conventions

- **PSR-12** pour le code PHP
- **Conventional Commits** pour les messages (`feat:`, `fix:`, `docs:`, `refactor:`)
- **PHPUnit** pour les tests (via `php artisan test`)
- PHPStan level 6 pour l'analyse statique

---

## License

MIT
