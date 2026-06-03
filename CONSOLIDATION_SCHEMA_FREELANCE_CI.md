# Plan de Consolidation — Schéma Unifié FreelanceCI
**Version :** 1.0  
**Date :** Juin 2026  
**Auteur :** Équipe technique FreelanceCI  
**Statut :** Planification

---

# db d'origine:

@Freelance_CI.sql

# db actuelle:

@Freelance_CI(1).sql

## Table des matières

1. [Contexte et objectifs](#1-contexte-et-objectifs)
2. [Principes directeurs](#2-principes-directeurs)
3. [Inventaire des divergences](#3-inventaire-des-divergences)
4. [Plan de consolidation par domaine](#4-plan-de-consolidation-par-domaine)
5. [Schéma unifié — décisions table par table](#5-schéma-unifié--décisions-table-par-table)
6. [Migrations à produire](#6-migrations-à-produire)
7. [Triggers et fonctions à rétablir](#7-triggers-et-fonctions-à-rétablir)
8. [Index de performance à rétablir](#8-index-de-performance-à-rétablir)
9. [Vues SQL à rétablir](#9-vues-sql-à-rétablir)
10. [Risques et points de vigilance](#10-risques-et-points-de-vigilance)
11. [Plan d'exécution phasé](#11-plan-dexécution-phasé)
12. [Checklist de validation finale](#12-checklist-de-validation-finale)

---

## 1. Contexte et objectifs

### Situation actuelle

Deux schémas coexistent après une migration partielle vers Laravel/Eloquent :

| Schéma | Fichier | Nature |
|---|---|---|
| **Original** | `Freelance_CI.sql` | Schéma PostgreSQL natif — riche, typé, avec ENUMs, triggers, vues |
| **Actuel** | *(collé dans le chat)* | Schéma généré par migrations Laravel — plus léger, moins contraint |

### Objectifs de la consolidation

- **Conserver** la richesse métier et la robustesse du schéma original
- **Conserver** les apports du schéma Laravel (referral, conversation_participants, compatibilité Eloquent)
- **Corriger** les régressions introduites par la migration Laravel
- **Unifier** en un seul schéma PostgreSQL compatible Laravel/Eloquent
- **Garantir** l'intégrité financière (wallet, paiements, escrow)

---

## 2. Principes directeurs

1. **La base de données est le dernier rempart de l'intégrité** — les contraintes métier critiques (ENUMs, FKs, UNIQUE) restent en base, pas seulement dans le code applicatif.
2. **Compatibilité Laravel** — les noms de tables restent en snake_case pluriel, les PKs gardent leur type `uuid`, les timestamps `created_at`/`updated_at` sont présents sur toutes les tables.
3. **Sémantique FCFA préservée** — tous les montants financiers sont en `NUMERIC(15,2)` avec `DEFAULT 'XOF'` explicite.
4. **Traçabilité Genius Pay non négociable** — le cycle de vie complet d'un paiement doit être reconstitutable depuis la base seule.
5. **Contrainte 1-1 stricte** — `wallets`, `freelance_profiles`, `client_profiles` ont une relation strictement un-à-un avec `users`, garantie en base.

---

## 3. Inventaire des divergences

### 3.1 Régressions critiques (à corriger en priorité 1)

| # | Table | Problème | Gravité |
|---|---|---|---|
| R1 | Toutes | ENUMs remplacés par `VARCHAR` sans contrainte | 🔴 Critique |
| R2 | `payments` | Perte de `payer_id`/`payee_id`, `genius_pay_transaction_id`, timestamps de cycle de vie | 🔴 Critique |
| R3 | `wallets` | PK changée → permet plusieurs wallets par user | 🔴 Critique |
| R4 | `wallet_transactions` | Perte de `payment_id` (lien traçable) | 🔴 Critique |
| R5 | `contracts` | `quote_id` devenu nullable — rompt le flux métier | 🔴 Critique |
| R6 | `payment_sync_log` | Sémantique cron perdue, remplacée par log générique | 🟠 Majeur |
| R7 | `genius_pay_webhooks` | `transaction_id` supprimé, perte du lien Genius Pay | 🟠 Majeur |

### 3.2 Pertes de fonctionnalités (à rétablir en priorité 2)

| # | Élément | Nature |
|---|---|---|
| P1 | Triggers | `update_wallet_on_payment_released`, `update_freelance_rating`, `update_project_quotes_count` |
| P2 | Vues | `v_freelance_listing`, `v_admin_dashboard`, `v_monthly_revenue`, `v_genius_pay_monitoring` |
| P3 | Index | Index GIN full-text, index partiels sur `deleted_at`, index sur paiements |
| P4 | `reviews` | Critères détaillés (`rating_quality`, `rating_delay`, `rating_communication`) remplacés par JSON opaque |
| P5 | `freelance_profiles` | Champs riches : `tagline`, `daily_rate_xof`, `availability_note`, `response_rate` |
| P6 | `milestones` | Champs `status`, `delivered_at`, `validated_at` présents dans l'actuel mais absents de l'original → à conserver |

### 3.3 Apports du schéma Laravel (à conserver)

| # | Élément | Valeur ajoutée |
|---|---|---|
| A1 | `referral_codes` + `referrals` | Nouveau système de parrainage — à intégrer |
| A2 | `conversation_participants` | Meilleure flexibilité messagerie (muting, last_read_at) |
| A3 | `auth_tokens` enrichi | `device_name`, `device_type`, `ip_address`, `user_agent` — utile pour la sécurité |
| A4 | `milestones` enrichi | `status`, `delivered_at`, `validated_at` — cycle de vie plus complet |
| A5 | `transactions` | Table de journal financier global — à articuler avec `payments` |
| A6 | `freelance_subscriptions` | `billing_cycle` (monthly/yearly), `auto_renew` — plus complet que l'original |

---

## 4. Plan de consolidation par domaine

### Domaine 1 — Utilisateurs & Authentification

**Décision :** Fusionner les deux approches.

- `users` : conserver les champs de l'actuel (`first_name`, `last_name`, `avatar_url` déplacés ici pour compatibilité Laravel) + ajouter `phone_verified_at` de l'original
- `profiles` : conserver pour les champs étendus (bio, ville, liens sociaux) — relation 1-1 via `user_id PRIMARY KEY`
- `auth_tokens` : prendre la version Laravel (plus riche en métadonnées device) mais ajouter `type` et `token_hash` de l'original
- `social_accounts` : prendre l'original (contrainte `UNIQUE(provider, provider_id)` + `access_token`)

### Domaine 2 — Profils freelance & client

**Décision :** Prendre l'original comme base, enrichir avec certains champs de l'actuel.

- `freelance_profiles` : PK = `user_id` (1-1 garanti) + tous les champs de l'original + ajouter `professional_title`, `education_level` de l'actuel
- `client_profiles` : PK = `user_id` (1-1 garanti) + fusionner `company_sector`/`industry`, `total_spent_xof`/`total_spent`
- `job_categories` : UUID (actuel) plutôt que SERIAL (original) pour cohérence globale, mais garder `icon_url` de l'original
- `skills` : ajouter `is_active` de l'original, garder UUID
- `freelance_skills` : PK composite `(freelance_id, skill_id)` de l'original — plus propre que l'UUID artificiel de l'actuel
- `freelance_languages` : idem, PK composite `(freelance_id, language)`

### Domaine 3 — Abonnements

**Décision :** Fusionner, prendre le meilleur des deux.

- `subscription_plans_config` : garder les champs de l'actuel (`price_monthly`, `price_yearly`, `billing_cycle`) + `name_fr`, `max_proposals` de l'original
- `freelance_subscriptions` : garder `billing_cycle`, `auto_renew` de l'actuel + lien `payment_id` de l'original

### Domaine 4 — Projets & Devis

**Décision :** Base = original, ajouts mineurs de l'actuel.

- `projects` : prendre l'original + ajouter `is_urgent`, `is_remote`, `location` de l'actuel ; remplacer `skills_required INT[]` par `required_skills JSONB` (plus flexible ET indexable)
- `quotes` : prendre l'original + ajouter `read_at`, `responded_at` de l'actuel ; maintenir `UNIQUE(project_id, freelance_id)`
- `contracts` : `quote_id NOT NULL` (restauré) + ajouter `platform_fee`, `freelance_amount` de l'actuel en remplacement des colonnes calculées de l'original

### Domaine 5 — Paiements (domaine le plus critique)

**Décision :** Repartir de l'original, intégrer `transactions` de l'actuel comme table de journal.

- `payments` : restaurer TOUS les champs Genius Pay de l'original + garder `transaction_id` (FK vers `transactions`) de l'actuel pour le journal
- `transactions` : conserver comme table de journal financier global (mouvement comptable)
- `escrows` : prendre l'original (UNIQUE sur `contract_id`) + ajouter `held_amount`, `released_amount`, `refunded_amount` de l'actuel
- `wallets` : restaurer `user_id PRIMARY KEY` (1-1 strict)
- `wallet_transactions` : restaurer `payment_id` + garder `reference` et `type` de l'actuel
- `withdrawal_requests` : fusionner — garder `genius_pay_transfer_id`, `bank_account JSONB` de l'original + `fee`, `net_amount` de l'actuel
- `invoices` : fusionner — garder `issued_to_id`, `pdf_url`, `tax_xof` de l'original + `issue_date`, `due_date` de l'actuel
- `payment_sync_log` : **deux tables séparées** — garder la table cron de l'original ET la table de log par paiement de l'actuel (renommer en `payment_action_log`)

### Domaine 6 — Messagerie

**Décision :** Hybrider les deux approches.

- `conversations` : prendre l'actuel (`conversation_participants` séparé) mais ajouter `UNIQUE(project_id, client_id, freelance_id)` pour éviter les doublons
- `conversation_participants` : conserver (apport positif de l'actuel)
- `messages` : fusionner — ajouter `delivered_at` de l'original, garder `deleted_at` de l'actuel

### Domaine 7 — Avis & Réputation

**Décision :** Prendre l'original, enrichir.

- `reviews` : restaurer `rating_quality`, `rating_delay`, `rating_communication` comme colonnes dédiées (plus requêtables que JSON) + conserver `criteria_ratings JSONB` pour critères additionnels futurs
- `review_replies` : prendre l'original (contrainte `UNIQUE(review_id)` — une seule réponse par avis)

### Domaine 8 — Signalements & Litiges

**Décision :** Prendre l'original (plus complet).

- `reports` : restaurer `reported_project_id` (permet de signaler un projet, pas seulement un user)
- `disputes` : l'original est plus propre (`opened_by` vs `raised_by`, `resolution_note` structurée)

### Domaine 9 — Boosts & Badges

**Décision :** Prendre l'original.

- `boosts` : restaurer `user_id`, `target boost_target`, `payment_id`
- `verified_badges` : restaurer `UNIQUE(freelance_id)` (un seul badge actif par freelance)

### Domaine 10 — Administration

**Décision :** Fusionner.

- `admin_logs` : prendre l'actuel (champs `old_values`/`new_values` JSON utiles) + ajouter `target_table` de l'original
- `platform_settings` : prendre l'original (PK = `key` VARCHAR — plus simple) + ajouter `is_public`, `group` de l'actuel

### Domaine 11 — Parrainage (nouveau)

**Décision :** Intégrer l'apport de l'actuel tel quel.

- `referral_codes` : conserver
- `referrals` : conserver avec `UNIQUE(referred_id)` (un parrainage par utilisateur inscrit)

---

## 5. Schéma unifié — décisions table par table

### Récapitulatif des décisions

| Table | Base retenue | Modifications |
|---|---|---|
| `users` | Actuel | + `phone_verified_at` |
| `social_accounts` | Original | + `provider_refresh_token` (actuel) |
| `auth_tokens` | Actuel | + `token_hash`, `type`, `used_at` (original) |
| `profiles` | Original | Inchangé |
| `client_profiles` | Original (PK=user_id) | + `industry`, `average_rating` (actuel) |
| `freelance_profiles` | Original (PK=user_id) | + `professional_title`, `education_level`, `hourly_rate_min/max` (actuel) |
| `job_categories` | Actuel (UUID) | + `icon_url` (original) |
| `skills` | Actuel (UUID) | + `is_active` (original) |
| `freelance_skills` | Original (PK composite) | Inchangé |
| `freelance_languages` | Original (PK composite) | Inchangé |
| `portfolio_items` | Original | + `is_featured`, `completed_date` (actuel) |
| `portfolio_files` | Fusionné | `file_size` en `INT` (kb) |
| `verifications` | Original | + `expires_at` (actuel) |
| `subscription_plans_config` | Fusionné | Garder `price_monthly`/`price_yearly` + `max_proposals`, `name_fr` |
| `freelance_subscriptions` | Fusionné | + `billing_cycle`, `auto_renew` (actuel) + `payment_id` (original) |
| `projects` | Original | + `is_urgent`, `is_remote`, `location` (actuel) ; `required_skills JSONB` |
| `project_files` | Original | + champs basiques |
| `quotes` | Original | + `read_at`, `responded_at` (actuel) |
| `contracts` | Original | `quote_id NOT NULL` restauré + `platform_fee`, `freelance_amount` (actuel) |
| `milestones` | Fusionné | + `status`, `delivered_at`, `validated_at` (actuel) |
| `payments` | **Original complet** | + `transaction_id` FK (lien journal) |
| `transactions` | Actuel | Conserver comme journal comptable global |
| `genius_pay_webhooks` | **Original** | `transaction_id` et `raw_payload JSONB` restaurés |
| `payment_sync_log` | **Original** | Renommer l'actuel en `payment_action_log` |
| `payment_action_log` | Actuel (renommé) | Log des actions par paiement individuel |
| `escrows` | Fusionné | `UNIQUE(contract_id)` + `held_amount`, `released_amount`, `refunded_amount` |
| `invoices` | Fusionné | `issued_to_id`, `pdf_url`, `tax_xof` + `due_date`, `paid_date` |
| `wallets` | **Original** | `user_id PRIMARY KEY` restauré |
| `wallet_transactions` | Fusionné | `payment_id` restauré + `type`, `reference` (actuel) |
| `withdrawal_requests` | Fusionné | `genius_pay_transfer_id`, `bank_account JSONB` + `fee`, `net_amount` |
| `conversations` | Actuel | + contrainte `UNIQUE` anti-doublon |
| `conversation_participants` | Actuel | Conservé |
| `messages` | Fusionné | + `delivered_at` (original) + `deleted_at` (actuel) |
| `message_files` | Fusionné | Inchangé |
| `notifications` | Fusionné | + `sent_email`, `sent_push` (original) + `action_url` (actuel) |
| `reviews` | Original | + critères détaillés + `criteria_ratings JSONB` pour extensibilité |
| `review_replies` | Original | `UNIQUE(review_id)` restauré |
| `reports` | Original | + `reported_project_id` |
| `disputes` | Original | Inchangé |
| `boosts` | Original | + `amount_paid`, `payment_reference` (actuel) |
| `verified_badges` | Original | + `verification_id` FK (actuel) ; `UNIQUE(freelance_id)` restauré |
| `admin_logs` | Actuel | + `target_table` (original) |
| `platform_settings` | Original (PK=key) | + `is_public`, `group` (actuel) |
| `referral_codes` | Actuel | Nouveau — conserver |
| `referrals` | Actuel | Nouveau — conserver |

---

## 6. Migrations à produire

Les migrations sont à créer dans l'ordre suivant pour respecter les dépendances entre tables.

### Phase A — Fondations

```
2026_06_01_001_fix_users_table.php
  → Ajouter phone_verified_at
  → Ajouter contrainte CHECK sur role IN ('client','freelance','admin')
  → Ajouter contrainte CHECK sur status IN ('pending','active','suspended','banned')

2026_06_01_002_fix_auth_tokens_table.php
  → Ajouter token_hash TEXT UNIQUE
  → Ajouter type VARCHAR(50)
  → Ajouter used_at TIMESTAMPTZ

2026_06_01_003_fix_social_accounts_table.php
  → Ajouter contrainte UNIQUE(provider, provider_id)
  → Ajouter access_token TEXT (remplace provider_token)
  → Ajouter expires_at
```

### Phase B — Profils

```
2026_06_01_010_fix_profiles_table.php
  → Ajouter display_name VARCHAR(150)
  → Ajouter birth_date → s'assurer cohérence avec gender

2026_06_01_011_fix_client_profiles_table.php
  → Changer PK en user_id (supprimer id UUID séparé)
  → Ajouter contrainte UNIQUE(user_id)
  → Renommer company_sector / industry → garder industry, ajouter company_sector

2026_06_01_012_fix_freelance_profiles_table.php
  → Changer PK en user_id (supprimer id UUID séparé)
  → Ajouter contrainte UNIQUE(user_id)
  → Ajouter tagline, daily_rate_xof, availability_note, response_rate
  → Renommer hourly_rate_min/max → hourly_rate_min_xof / hourly_rate_max_xof

2026_06_01_013_fix_freelance_skills_table.php
  → Changer PK en composite (freelance_profile_id, skill_id) → (freelance_id, skill_id)
  → Supprimer id UUID superflu

2026_06_01_014_fix_freelance_languages_table.php
  → Changer PK en composite (freelance_id, language)
  → Supprimer id UUID superflu

2026_06_01_015_fix_skills_table.php
  → Ajouter is_active BOOLEAN DEFAULT TRUE
```

### Phase C — Projets & Devis

```
2026_06_01_020_fix_projects_table.php
  → Ajouter featured_until TIMESTAMPTZ
  → Ajouter selected_quote_id UUID nullable (FK ajoutée après quotes)
  → Changer required_skills JSON → JSONB

2026_06_01_021_fix_quotes_table.php
  → Ajouter contrainte UNIQUE(project_id, freelance_id)
  → Ajouter accepted_at, refused_at, withdrawn_at
  → Ajouter cover_letter (alias de proposal)

2026_06_01_022_fix_contracts_table.php
  → Mettre quote_id NOT NULL (restauration contrainte métier)
  → Ajouter commission_rate NUMERIC(5,2)
  → Ajouter commission_xof NUMERIC(12,2)
  → Ajouter completed_at, cancelled_at
  → Ajouter terms_text TEXT (snapshot CGU au moment de signature)

2026_06_01_023_fix_milestones_table.php
  → Ajouter is_completed BOOLEAN (présent dans actuel, absent original)
  → Harmoniser status CHECK IN ('pending','in_progress','delivered','validated','cancelled')
```

### Phase D — Paiements (critique — à faire avec backup préalable)

```
2026_06_01_030_rebuild_payments_table.php
  → Ajouter payer_id UUID NOT NULL REFERENCES users(id)
  → Ajouter payee_id UUID REFERENCES users(id)
  → Ajouter genius_pay_transaction_id VARCHAR(255) UNIQUE
  → Ajouter genius_pay_status VARCHAR(20) CHECK IN ('PENDING','SUCCESS','FAILED','CANCELLED','EXPIRED')
  → Ajouter payment_channel VARCHAR(20) CHECK IN ('MOBILE_MONEY','CARD','BANK_TRANSFER','USSD')
  → Ajouter operator_id VARCHAR(20) CHECK IN ('ORANGE','MTN','MOOV','WAVE','CARD','UNKNOWN')
  → Ajouter customer_phone, customer_email
  → Ajouter gross_amount_xof, commission_xof (renommer net_amount → net_amount_xof)
  → Ajouter initiated_at, confirmed_at, failed_at, refunded_at
  → Ajouter provider_response JSONB
  → Migrer user_id → payer_id (ATTENTION : migration des données)

2026_06_01_031_rebuild_genius_pay_webhooks_table.php
  → Ajouter transaction_id VARCHAR(255) NOT NULL
  → Changer payload JSON → raw_payload JSONB
  → Ajouter processed_by VARCHAR(255)
  → Créer INDEX sur transaction_id

2026_06_01_032_rebuild_payment_sync_log_table.php
  → Garder la table actuelle renommée en payment_action_log
  → Créer nouvelle payment_sync_log avec : sync_date, start_time, end_time,
    total_checked, total_updated, total_failed, status, error_details

2026_06_01_033_fix_escrows_table.php
  → Ajouter UNIQUE(contract_id)
  → Ajouter release_requested_at
  → Ajouter dispute_id UUID (FK après disputes)
  → S'assurer que payment_id est NOT NULL

2026_06_01_034_rebuild_wallets_table.php
  → ⚠️  CRITIQUE : changer PK de id UUID → user_id UUID PRIMARY KEY
  → Renommer balance → available_xof
  → Renommer pending_balance → pending_xof
  → Renommer total_earned → total_earned_xof
  → Ajouter total_withdrawn_xof
  → Supprimer id UUID artificiel
  → MIGRATION DES DONNÉES requise

2026_06_01_035_fix_wallet_transactions_table.php
  → Ajouter payment_id UUID REFERENCES payments(id)
  → Ajouter direction VARCHAR(10) CHECK IN ('credit','debit')
  → Renommer amount → amount_xof
  → Renommer balance_after → balance_after_xof
  → Changer wallet_id → référencer wallets(user_id)

2026_06_01_036_fix_invoices_table.php
  → Ajouter issued_to_id UUID REFERENCES users(id)
  → Ajouter pdf_url TEXT
  → Ajouter tax_xof NUMERIC(12,2) DEFAULT 0
  → Renommer amount → total_xof
  → Ajouter trigger de génération invoice_number automatique

2026_06_01_037_fix_withdrawal_requests_table.php
  → Ajouter genius_pay_transfer_id VARCHAR(255)
  → Ajouter bank_account JSONB
  → Ajouter phone_number VARCHAR(30)
  → Renommer method → withdrawal_method
  → Ajouter CHECK IN ('orange_money','mtn_momo','wave','bank_transfer')
```

### Phase E — Messagerie & Notifications

```
2026_06_01_040_fix_conversations_table.php
  → Ajouter client_id UUID REFERENCES users(id)
  → Ajouter freelance_id UUID REFERENCES users(id)
  → Ajouter UNIQUE(project_id, client_id, freelance_id)

2026_06_01_041_fix_messages_table.php
  → Ajouter delivered_at TIMESTAMPTZ
  → S'assurer que deleted_at est présent

2026_06_01_042_fix_notifications_table.php
  → Ajouter sent_email BOOLEAN DEFAULT FALSE
  → Ajouter sent_push BOOLEAN DEFAULT FALSE
  → Ajouter CHECK sur type IN ('message','offer','payment','project','review','system','alert')
```

### Phase F — Réputation, Litiges, Boosts

```
2026_06_01_050_fix_reviews_table.php
  → Restaurer rating_quality SMALLINT CHECK (1-5)
  → Restaurer rating_delay SMALLINT CHECK (1-5)
  → Restaurer rating_communication SMALLINT CHECK (1-5)
  → Garder criteria_ratings JSONB pour extensibilité
  → Ajouter UNIQUE(contract_id) (un avis par contrat)
  → Ajouter is_public BOOLEAN DEFAULT TRUE

2026_06_01_051_fix_review_replies_table.php
  → Ajouter UNIQUE(review_id) (une seule réponse par avis)

2026_06_01_052_fix_reports_table.php
  → Ajouter reported_project_id UUID REFERENCES projects(id)
  → Ajouter CHECK sur type IN ('profil','comportement','contenu','fraude','autre')

2026_06_01_053_fix_boosts_table.php
  → Restaurer user_id UUID REFERENCES users(id)
  → Restaurer target CHECK IN ('profile','project')
  → Restaurer payment_id UUID REFERENCES payments(id)
  → Renommer freelance_profile_id → user_id

2026_06_01_054_fix_verified_badges_table.php
  → Ajouter UNIQUE(freelance_id) (un seul badge actif par freelance)
  → Restaurer payment_id REFERENCES payments(id)
  → Restaurer price_xof
```

### Phase G — Finalisations

```
2026_06_01_060_fix_platform_settings_table.php
  → Changer PK en key VARCHAR(100) PRIMARY KEY
  → Ajouter updated_by UUID REFERENCES users(id)
  → Garder is_public, group (actuel)
  → Insérer les données initiales (commission_rate, Genius Pay settings, etc.)

2026_06_01_061_add_referral_system.php
  → referral_codes : inchangé (déjà bien structuré)
  → referrals : inchangé

2026_06_01_070_add_performance_indexes.php
  → Voir section 8

2026_06_01_080_add_triggers_and_functions.php
  → Voir section 7

2026_06_01_090_create_views.php
  → Voir section 9
```

---

## 7. Triggers et fonctions à rétablir

### 7.1 Mise à jour automatique de `updated_at`

```sql
-- À appliquer sur : users, profiles, freelance_profiles, client_profiles,
-- projects, quotes, contracts, payments, wallets, conversations, messages

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

### 7.2 Calcul automatique de la note moyenne freelance

```sql
CREATE OR REPLACE FUNCTION update_freelance_rating()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE freelance_profiles
    SET
        average_rating = (
            SELECT ROUND(AVG(rating)::NUMERIC, 2)
            FROM reviews WHERE reviewed_id = NEW.reviewed_id
        ),
        total_reviews = (
            SELECT COUNT(*) FROM reviews WHERE reviewed_id = NEW.reviewed_id
        )
    WHERE user_id = NEW.reviewed_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger : AFTER INSERT OR UPDATE ON reviews
```

### 7.3 Compteur de devis sur les projets

```sql
CREATE OR REPLACE FUNCTION update_project_quotes_count()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE projects SET quotes_count = quotes_count + 1 WHERE id = NEW.project_id;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE projects SET quotes_count = quotes_count - 1 WHERE id = OLD.project_id;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Trigger : AFTER INSERT et AFTER DELETE ON quotes
```

### 7.4 Créditement automatique du wallet à la libération de l'escrow

```sql
CREATE OR REPLACE FUNCTION credit_wallet_on_escrow_release()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'released' AND OLD.status != 'released' THEN
        -- Récupérer le freelance via le contrat
        INSERT INTO wallets (user_id, available_xof, pending_xof, total_earned_xof, updated_at)
        SELECT 
            c.freelance_id,
            NEW.net_amount_xof,
            0,
            NEW.net_amount_xof,
            NOW()
        FROM payments NEW_P
        JOIN contracts c ON c.id = NEW_P.contract_id
        WHERE NEW_P.id = NEW.payment_id
        ON CONFLICT (user_id) DO UPDATE
        SET
            available_xof    = wallets.available_xof + EXCLUDED.available_xof,
            pending_xof      = GREATEST(0, wallets.pending_xof - EXCLUDED.available_xof),
            total_earned_xof = wallets.total_earned_xof + EXCLUDED.total_earned_xof,
            updated_at       = NOW();
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger : AFTER UPDATE ON escrows
```

### 7.5 Génération automatique du numéro de facture

```sql
CREATE SEQUENCE IF NOT EXISTS invoice_seq START 1;

CREATE OR REPLACE FUNCTION generate_invoice_number()
RETURNS TRIGGER AS $$
BEGIN
    NEW.invoice_number = 'INV-' 
        || TO_CHAR(NOW(), 'YYYY') || '-' 
        || LPAD(nextval('invoice_seq')::TEXT, 6, '0');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger : BEFORE INSERT ON invoices WHEN (invoice_number IS NULL)
```

### 7.6 Mise à jour du dernier message dans la conversation

```sql
CREATE OR REPLACE FUNCTION update_conversation_last_message()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE conversations SET last_message_at = NEW.created_at WHERE id = NEW.conversation_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger : AFTER INSERT ON messages
```

---

## 8. Index de performance à rétablir

```sql
-- Utilisateurs
CREATE INDEX idx_users_role         ON users(role);
CREATE INDEX idx_users_status       ON users(status);
CREATE INDEX idx_users_deleted      ON users(deleted_at) WHERE deleted_at IS NULL;

-- Profils freelance
CREATE INDEX idx_fp_available       ON freelance_profiles(is_available) WHERE is_available = TRUE;
CREATE INDEX idx_fp_verified        ON freelance_profiles(is_verified);
CREATE INDEX idx_fp_rating          ON freelance_profiles(average_rating DESC);
CREATE INDEX idx_fp_rate            ON freelance_profiles(daily_rate_xof);

-- Projets
CREATE INDEX idx_projects_status    ON projects(status);
CREATE INDEX idx_projects_client    ON projects(client_id);
CREATE INDEX idx_projects_category  ON projects(category_id);
CREATE INDEX idx_projects_featured  ON projects(is_featured) WHERE is_featured = TRUE;
CREATE INDEX idx_projects_created   ON projects(created_at DESC);
CREATE INDEX idx_projects_deleted   ON projects(deleted_at) WHERE deleted_at IS NULL;

-- Devis
CREATE INDEX idx_quotes_project     ON quotes(project_id);
CREATE INDEX idx_quotes_freelance   ON quotes(freelance_id);
CREATE INDEX idx_quotes_status      ON quotes(status);

-- Paiements Genius Pay (critiques)
CREATE INDEX idx_payments_payer          ON payments(payer_id);
CREATE INDEX idx_payments_payee          ON payments(payee_id);
CREATE INDEX idx_payments_status         ON payments(status);
CREATE INDEX idx_payments_contract       ON payments(contract_id);
CREATE INDEX idx_payments_genius_id      ON payments(genius_pay_transaction_id);
CREATE INDEX idx_payments_initiated      ON payments(initiated_at DESC);
CREATE INDEX idx_payments_channel        ON payments(payment_channel);

-- Webhooks
CREATE INDEX idx_webhook_transaction     ON genius_pay_webhooks(transaction_id);
CREATE INDEX idx_webhook_status          ON genius_pay_webhooks(status);
CREATE INDEX idx_webhook_created         ON genius_pay_webhooks(created_at DESC);

-- Messagerie
CREATE INDEX idx_messages_conv      ON messages(conversation_id);
CREATE INDEX idx_messages_sender    ON messages(sender_id);
CREATE INDEX idx_messages_created   ON messages(created_at DESC);

-- Notifications
CREATE INDEX idx_notifs_unread      ON notifications(user_id) WHERE is_read = FALSE;
CREATE INDEX idx_notifs_created     ON notifications(created_at DESC);

-- Avis
CREATE INDEX idx_reviews_reviewed   ON reviews(reviewed_id);
CREATE INDEX idx_reviews_rating     ON reviews(rating);

-- Boosts actifs
CREATE INDEX idx_boosts_active      ON boosts(target_id, ends_at) WHERE is_active = TRUE;

-- Full-text search (nécessite extension unaccent)
CREATE EXTENSION IF NOT EXISTS "unaccent";

CREATE INDEX idx_freelance_fts ON profiles USING GIN (
    to_tsvector('french', unaccent(
        coalesce(first_name,'') || ' ' || coalesce(last_name,'') || ' ' || coalesce(bio,'')
    ))
);

CREATE INDEX idx_projects_fts ON projects USING GIN (
    to_tsvector('french', unaccent(title || ' ' || coalesce(description,'')))
);
```

---

## 9. Vues SQL à rétablir

### 9.1 `v_freelance_listing` — Listing public des freelances

```sql
CREATE OR REPLACE VIEW v_freelance_listing AS
SELECT
    u.id,
    p.display_name,
    p.first_name,
    p.last_name,
    p.avatar_url,
    p.city,
    p.country,
    fp.tagline,
    fp.daily_rate_xof,
    fp.hourly_rate_min_xof,
    fp.average_rating,
    fp.total_reviews,
    fp.missions_completed,
    fp.is_available,
    fp.is_verified,
    jc.name   AS category_name,
    jc.slug   AS category_slug,
    EXISTS (
        SELECT 1 FROM boosts b
        WHERE b.target_id = u.id AND b.target = 'profile'
          AND b.is_active AND b.ends_at > NOW()
    ) AS is_boosted
FROM users u
JOIN profiles p             ON p.user_id = u.id
JOIN freelance_profiles fp  ON fp.user_id = u.id
LEFT JOIN job_categories jc ON jc.id = fp.category_id
WHERE u.role = 'freelance'
  AND u.status = 'active'
  AND u.deleted_at IS NULL;
```

### 9.2 `v_admin_dashboard` — Tableau de bord administration

```sql
CREATE OR REPLACE VIEW v_admin_dashboard AS
SELECT
    (SELECT COUNT(*) FROM users WHERE role = 'freelance' AND status = 'active')   AS active_freelances,
    (SELECT COUNT(*) FROM users WHERE role = 'client'    AND status = 'active')   AS active_clients,
    (SELECT COUNT(*) FROM projects WHERE status = 'open')                         AS open_projects,
    (SELECT COUNT(*) FROM projects WHERE status = 'in_progress')                  AS projects_in_progress,
    (SELECT COUNT(*) FROM contracts WHERE status = 'completed')                   AS completed_missions,
    (SELECT COALESCE(SUM(commission_xof), 0) FROM payments WHERE status = 'released') AS total_commissions_xof,
    (SELECT COUNT(*) FROM verifications WHERE status = 'pending')                 AS pending_verifications,
    (SELECT COUNT(*) FROM disputes WHERE status IN ('open','under_review'))       AS open_disputes,
    (SELECT COUNT(*) FROM reports WHERE status = 'open')                          AS open_reports,
    (SELECT COUNT(*) FROM withdrawal_requests WHERE status = 'pending')           AS pending_withdrawals;
```

### 9.3 `v_monthly_revenue` — Revenus mensuels par type

```sql
CREATE OR REPLACE VIEW v_monthly_revenue AS
SELECT
    DATE_TRUNC('month', initiated_at) AS month,
    type,
    COUNT(*)                          AS nb_transactions,
    SUM(commission_xof)               AS commissions_xof,
    SUM(gross_amount_xof)             AS gross_xof,
    SUM(net_amount_xof)               AS net_xof
FROM payments
WHERE status IN ('released', 'confirmed')
GROUP BY 1, 2
ORDER BY 1 DESC, 2;
```

### 9.4 `v_genius_pay_monitoring` — Suivi paiements Genius Pay

```sql
CREATE OR REPLACE VIEW v_genius_pay_monitoring AS
SELECT
    p.id,
    p.genius_pay_transaction_id,
    p.genius_pay_status,
    p.payment_channel,
    p.operator_id,
    p.gross_amount_xof,
    p.status AS internal_status,
    p.initiated_at,
    p.confirmed_at,
    CASE
        WHEN p.confirmed_at IS NULL AND p.initiated_at < NOW() - INTERVAL '1 hour' THEN 'TIMEOUT'
        WHEN p.genius_pay_status = 'SUCCESS' AND p.status != 'released'            THEN 'DESYNC'
        ELSE 'OK'
    END AS alert_status
FROM payments p
WHERE p.genius_pay_transaction_id IS NOT NULL;
```

### 9.5 `v_wallet_summary` — Résumé financier par freelance (nouvelle vue)

```sql
CREATE OR REPLACE VIEW v_wallet_summary AS
SELECT
    u.id AS user_id,
    p.first_name,
    p.last_name,
    w.available_xof,
    w.pending_xof,
    w.total_earned_xof,
    w.total_withdrawn_xof,
    (w.available_xof + w.pending_xof) AS total_balance_xof,
    COUNT(wr.id) FILTER (WHERE wr.status = 'pending') AS pending_withdrawals
FROM wallets w
JOIN users u     ON u.id = w.user_id
JOIN profiles p  ON p.user_id = u.id
LEFT JOIN withdrawal_requests wr ON wr.user_id = u.id
GROUP BY u.id, p.first_name, p.last_name, w.available_xof, w.pending_xof, w.total_earned_xof, w.total_withdrawn_xof;
```

---

## 10. Risques et points de vigilance

### 🔴 Risques critiques

| Risque | Description | Mitigation |
|---|---|---|
| **Migration wallets** | Changer la PK de `id` → `user_id` nécessite de migrer les données existantes et de vérifier qu'il n'existe pas de doublons user_id | Vérifier `SELECT user_id, COUNT(*) FROM wallets GROUP BY user_id HAVING COUNT(*) > 1` avant migration |
| **Migration payments** | Ajouter `payer_id` non-nullable requiert de mapper `user_id` → `payer_id` pour toutes les lignes existantes | Identifier les paiements d'abonnement (pas de payee) vs paiements de mission |
| **Contrainte quote_id NOT NULL** | Des contrats existants sans `quote_id` bloqueront la migration | Recenser les contrats orphelins et les traiter avant d'appliquer la contrainte |
| **Renommer `freelance_profile_id`** | Les tables `boosts`, `freelance_skills`, `freelance_subscriptions` référencent `freelance_profile_id` avec une FK vers `freelance_profiles(id)`. Après passage à PK=user_id, ces FKs doivent pointer vers `users(id)` | Mettre à jour toutes les FKs en cascade |

### 🟠 Risques majeurs

| Risque | Description | Mitigation |
|---|---|---|
| **PK composite sur freelance_skills** | Si des doublons `(freelance_id, skill_id)` existent en base, la création de la PK composite échouera | `SELECT freelance_profile_id, skill_id, COUNT(*) FROM freelance_skills GROUP BY 1,2 HAVING COUNT(*)>1` |
| **Triggers et performances** | Le trigger `update_wallet_on_payment_released` exécute des INSERTs/UPDATEs sur libération d'escrow — tester sous charge | Tests de charge avant passage en production |
| **Compatibilité Eloquent** | Les modèles Laravel pointent vers l'ancienne structure. Après migration, tous les modèles et relations doivent être mis à jour | Identifier tous les `BelongsTo`, `HasOne`, `HasMany` impactés |

### 🟡 Points de vigilance

- Les colonnes `billing_cycle` et `auto_renew` sur `freelance_subscriptions` introduisent une logique de renouvellement automatique qui n'existait pas — prévoir le cron correspondant
- La coexistence de `transactions` et `payments` crée un risque de confusion dans le code applicatif — documenter clairement le rôle de chaque table (payments = opération Genius Pay, transactions = journal comptable interne)
- Les vues SQL ne sont pas visibles depuis Laravel par défaut — les déclarer comme modèles read-only ou utiliser `DB::select()`

---

## 11. Plan d'exécution phasé

### Sprint 1 — Préparation (1 semaine)

- [ ] Créer un environnement de staging avec copie de la base de production
- [ ] Écrire et exécuter les requêtes de détection de doublons (wallets, freelance_skills, contracts orphelins)
- [ ] Documenter tous les modèles Eloquent impactés
- [ ] Rédiger les migrations Phase A (users, auth, social)
- [ ] Tests unitaires sur les migrations Phase A

### Sprint 2 — Fondations & Profils (1 semaine)

- [ ] Appliquer migrations Phase A et Phase B sur staging
- [ ] Mettre à jour modèles Eloquent : `User`, `Profile`, `FreelanceProfile`, `ClientProfile`
- [ ] Vérifier les relations `HasOne`/`BelongsTo` sur les profils
- [ ] Tests fonctionnels : inscription, connexion, profil

### Sprint 3 — Projets & Contrats (1 semaine)

- [ ] Appliquer migrations Phase C sur staging
- [ ] Restaurer la contrainte `quote_id NOT NULL` (après traitement des orphelins)
- [ ] Mettre à jour modèles : `Project`, `Quote`, `Contract`, `Milestone`
- [ ] Tests fonctionnels : création projet, envoi devis, signature contrat

### Sprint 4 — Paiements (2 semaines — phase la plus critique)

- [ ] Backup complet de la base avant toute opération
- [ ] Appliquer migrations Phase D sur staging
- [ ] Migration des données `payments.user_id` → `payer_id`
- [ ] Reconstruction `wallets` (PK change)
- [ ] Mettre à jour modèles : `Payment`, `Escrow`, `Wallet`, `WalletTransaction`, `Invoice`
- [ ] Tests complets de bout en bout : paiement, escrow, libération, wallet crédit
- [ ] Validation avec l'API Genius Pay (mode test)
- [ ] Tests de charge

### Sprint 5 — Messagerie, Notifs, Réputation (1 semaine)

- [ ] Appliquer migrations Phase E et F
- [ ] Mettre à jour modèles : `Conversation`, `Message`, `Review`, `Report`, `Dispute`
- [ ] Tester le flux de messagerie complet
- [ ] Tester création d'avis post-contrat

### Sprint 6 — Finalisation (1 semaine)

- [ ] Appliquer migrations Phase G (index, triggers, vues)
- [ ] Ajouter le système de referral
- [ ] Insérer les données initiales (`platform_settings`, `job_categories`, `subscription_plans_config`)
- [ ] Recette fonctionnelle complète sur staging
- [ ] Audit de performance (EXPLAIN ANALYZE sur les requêtes critiques)
- [ ] **Passage en production** (heure creuse, avec fenêtre de maintenance)

---

## 12. Checklist de validation finale

### Intégrité structurelle
- [ ] Toutes les FKs pointent vers des tables/colonnes qui existent
- [ ] Les contraintes `NOT NULL` métier critiques sont en place (`quote_id` sur contracts, etc.)
- [ ] Les contraintes `UNIQUE` sont appliquées (wallet user_id, badge freelance_id, etc.)
- [ ] Les contraintes `CHECK` remplacent les ENUMs disparus sur les colonnes statuts

### Intégrité financière
- [ ] Un seul wallet par utilisateur (vérifier via COUNT)
- [ ] Chaque paiement a un `payer_id` renseigné
- [ ] Chaque escrow est lié à un contrat unique (`UNIQUE(contract_id)`)
- [ ] Les triggers de wallet sont actifs et testés

### Traçabilité Genius Pay
- [ ] `genius_pay_transaction_id` présent et indexé sur `payments`
- [ ] `transaction_id` présent et indexé sur `genius_pay_webhooks`
- [ ] La vue `v_genius_pay_monitoring` retourne des résultats cohérents
- [ ] La table `payment_sync_log` (cron) est distincte de `payment_action_log`

### Compatibilité applicative
- [ ] Tous les modèles Eloquent mis à jour
- [ ] Les `$fillable` et `$casts` reflètent le nouveau schéma
- [ ] Les seeders de données initiales fonctionnent
- [ ] Les tests PHPUnit passent sans erreur

### Performance
- [ ] Les index critiques sont en place (vérifier via `\d tablename` ou `pg_indexes`)
- [ ] Les index GIN full-text sont actifs
- [ ] `EXPLAIN ANALYZE` sur la requête de listing freelance < 50ms
- [ ] `EXPLAIN ANALYZE` sur la requête de listing projets < 50ms

---

*Document vivant — à mettre à jour au fur et à mesure de l'avancement des sprints.*
