-- ============================================================
--  FREELANCE CI — Schéma PostgreSQL Complet
--  Basé sur le cahier des charges v1
--  Intégration : Genius Pay (agrégateur paiements)
--  Auth sociales : Google, LinkedIn, GitHub
--  Encodage : UTF-8 | Locale : fr_CI
-- ============================================================

-- Extensions utiles
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- ============================================================
-- 0. TYPES ÉNUMÉRÉS
-- ============================================================

CREATE TYPE user_role         AS ENUM ('client', 'freelance', 'admin');
CREATE TYPE account_status    AS ENUM ('pending', 'active', 'suspended', 'banned');
CREATE TYPE gender_type       AS ENUM ('homme', 'femme', 'autre', 'non_precise');

CREATE TYPE subscription_plan AS ENUM ('starter', 'pro', 'expert');
CREATE TYPE sub_status        AS ENUM ('active', 'cancelled', 'expired', 'trial');

CREATE TYPE project_status    AS ENUM ('draft', 'open', 'in_progress', 'delivered', 'completed', 'cancelled', 'disputed');
CREATE TYPE quote_status      AS ENUM ('pending', 'accepted', 'refused', 'withdrawn');
CREATE TYPE contract_status   AS ENUM ('draft', 'signed', 'completed', 'cancelled');

-- Genius Pay : un seul agrégateur, plusieurs canaux
CREATE TYPE payment_channel   AS ENUM ('MOBILE_MONEY', 'CARD', 'BANK_TRANSFER', 'USSD');
CREATE TYPE payment_operator  AS ENUM ('ORANGE', 'MTN', 'MOOV', 'WAVE', 'CARD', 'UNKNOWN');
CREATE TYPE payment_status    AS ENUM ('pending', 'held', 'released', 'refunded', 'failed', 'cancelled');
CREATE TYPE transaction_type  AS ENUM ('mission', 'subscription', 'boost_profile', 'boost_project', 'badge_verified', 'ad', 'refund');
CREATE TYPE genius_pay_status AS ENUM ('PENDING', 'SUCCESS', 'FAILED', 'CANCELLED', 'EXPIRED');

CREATE TYPE escrow_status     AS ENUM ('holding', 'released', 'refunded', 'disputed');

CREATE TYPE message_status    AS ENUM ('sent', 'delivered', 'read');
CREATE TYPE notif_type        AS ENUM ('message', 'offer', 'payment', 'project', 'review', 'system', 'alert');

CREATE TYPE report_type       AS ENUM ('profil', 'comportement', 'contenu', 'fraude', 'autre');
CREATE TYPE report_status     AS ENUM ('open', 'under_review', 'resolved', 'dismissed');

CREATE TYPE dispute_status    AS ENUM ('open', 'under_review', 'resolved_client', 'resolved_freelance', 'closed');

CREATE TYPE boost_target      AS ENUM ('profile', 'project');
CREATE TYPE boost_duration    AS ENUM ('7_days', '30_days');

CREATE TYPE verification_type AS ENUM ('identity', 'portfolio', 'diploma', 'professional');
CREATE TYPE verif_status      AS ENUM ('pending', 'approved', 'rejected');

-- Types pour retraits
CREATE TYPE withdrawal_method AS ENUM ('orange_money', 'mtn_momo', 'wave', 'bank_transfer');


-- ============================================================
-- 1. UTILISATEURS & AUTHENTIFICATION
-- ============================================================

CREATE TABLE users (
    id                  UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email               VARCHAR(255) NOT NULL UNIQUE,
    phone               VARCHAR(30),
    password_hash       TEXT,                          -- NULL si auth sociale
    role                user_role NOT NULL DEFAULT 'client',
    status              account_status NOT NULL DEFAULT 'pending',
    email_verified_at   TIMESTAMPTZ,
    phone_verified_at   TIMESTAMPTZ,
    last_login_at       TIMESTAMPTZ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ                    -- soft delete
);

-- Auth sociale (Google, LinkedIn, GitHub)
CREATE TABLE social_accounts (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id     UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider    VARCHAR(50) NOT NULL,                  -- 'google' | 'linkedin' | 'github'
    provider_id VARCHAR(255) NOT NULL,
    access_token TEXT,
    expires_at  TIMESTAMPTZ,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (provider, provider_id)
);

-- Tokens (reset mot de passe, email verify, refresh JWT)
CREATE TABLE auth_tokens (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id     UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash  TEXT NOT NULL UNIQUE,
    type        VARCHAR(50) NOT NULL,                  -- 'password_reset' | 'email_verify' | 'refresh'
    expires_at  TIMESTAMPTZ NOT NULL,
    used_at     TIMESTAMPTZ,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- 2. PROFILS
-- ============================================================

-- Profil commun (étend users)
CREATE TABLE profiles (
    user_id         UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    first_name      VARCHAR(100),
    last_name       VARCHAR(100),
    display_name    VARCHAR(150),
    avatar_url      TEXT,
    gender          gender_type,
    city            VARCHAR(100),
    country         VARCHAR(100) DEFAULT 'Côte d''Ivoire',
    bio             TEXT,
    website_url     TEXT,
    linkedin_url    TEXT,
    github_url      TEXT,                               -- Ajout pour GitHub
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Profil CLIENT
CREATE TABLE client_profiles (
    user_id         UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    company_name    VARCHAR(200),
    company_sector  VARCHAR(150),
    company_size    VARCHAR(50),                       -- 'solo', '2-10', '11-50', '50+'
    siret           VARCHAR(50),
    total_spent_xof NUMERIC(15,2) DEFAULT 0,
    projects_count  INT DEFAULT 0,
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Catégories métiers (Dev, Design, Marketing, etc.)
CREATE TABLE job_categories (
    id          SERIAL PRIMARY KEY,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    icon_url    TEXT,
    parent_id   INT REFERENCES job_categories(id),
    sort_order  INT DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE
);

-- Compétences
CREATE TABLE skills (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    category_id INT REFERENCES job_categories(id),
    is_active   BOOLEAN DEFAULT TRUE
);

-- Profil FREELANCE
CREATE TABLE freelance_profiles (
    user_id             UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    tagline             VARCHAR(255),
    category_id         INT REFERENCES job_categories(id),
    experience_years    SMALLINT DEFAULT 0,
    daily_rate_xof      NUMERIC(10,2),                 -- Taux journalier en FCFA
    hourly_rate_xof     NUMERIC(10,2),
    is_available        BOOLEAN DEFAULT TRUE,
    availability_note   TEXT,                          -- ex : "Dispo à partir du 15 juin"
    is_verified         BOOLEAN DEFAULT FALSE,
    verified_at         TIMESTAMPTZ,
    average_rating      NUMERIC(3,2) DEFAULT 0,
    total_reviews       INT DEFAULT 0,
    total_earned_xof    NUMERIC(15,2) DEFAULT 0,
    missions_completed  INT DEFAULT 0,
    response_rate       NUMERIC(5,2) DEFAULT 0,        -- % de réponse aux messages
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Relation freelance ↔ compétences
CREATE TABLE freelance_skills (
    freelance_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    skill_id     INT NOT NULL REFERENCES skills(id) ON DELETE CASCADE,
    level        VARCHAR(20),                          -- 'débutant','intermédiaire','expert'
    PRIMARY KEY (freelance_id, skill_id)
);

-- Langues parlées
CREATE TABLE freelance_languages (
    freelance_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    language     VARCHAR(50) NOT NULL,
    level        VARCHAR(30),                          -- 'natif','courant','intermédiaire'
    PRIMARY KEY (freelance_id, language)
);

-- Portfolio
CREATE TABLE portfolio_items (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    freelance_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    category_id     INT REFERENCES job_categories(id),
    cover_url       TEXT,
    project_url     TEXT,
    tags            TEXT[],
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Fichiers/médias du portfolio
CREATE TABLE portfolio_files (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    item_id         UUID NOT NULL REFERENCES portfolio_items(id) ON DELETE CASCADE,
    file_url        TEXT NOT NULL,
    file_type       VARCHAR(50),
    file_size_kb    INT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Vérification d'identité / compétences
CREATE TABLE verifications (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    freelance_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type            verification_type NOT NULL,
    document_url    TEXT,
    status          verif_status NOT NULL DEFAULT 'pending',
    reviewer_id     UUID REFERENCES users(id),
    reviewed_at     TIMESTAMPTZ,
    rejection_note  TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- 3. ABONNEMENTS FREELANCE
-- ============================================================

CREATE TABLE subscription_plans_config (
    id              SERIAL PRIMARY KEY,
    plan            subscription_plan NOT NULL UNIQUE,
    name_fr         VARCHAR(100) NOT NULL,
    price_xof       NUMERIC(10,2) NOT NULL,
    max_proposals   INT,                               -- NULL = illimité
    featured_slots  INT DEFAULT 0,
    has_badge       BOOLEAN DEFAULT FALSE,
    description     TEXT,
    is_active       BOOLEAN DEFAULT TRUE
);

CREATE TABLE freelance_subscriptions (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    freelance_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    plan            subscription_plan NOT NULL,
    status          sub_status NOT NULL DEFAULT 'active',
    price_xof       NUMERIC(10,2) NOT NULL,
    started_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    expires_at      TIMESTAMPTZ NOT NULL,
    cancelled_at    TIMESTAMPTZ,
    payment_id      UUID,                              -- lié à payments (FK ajoutée plus bas)
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- 4. PROJETS & OFFRES
-- ============================================================

CREATE TABLE projects (
    id                  UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    client_id           UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id         INT REFERENCES job_categories(id),
    title               VARCHAR(300) NOT NULL,
    description         TEXT NOT NULL,
    budget_min_xof      NUMERIC(12,2),
    budget_max_xof      NUMERIC(12,2),
    deadline            DATE,
    skills_required     INT[],                         -- tableau de skill IDs
    status              project_status NOT NULL DEFAULT 'draft',
    is_featured         BOOLEAN DEFAULT FALSE,
    featured_until      TIMESTAMPTZ,
    views_count         INT DEFAULT 0,
    quotes_count        INT DEFAULT 0,
    selected_quote_id   UUID,                          -- FK ajoutée après création quotes
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMPTZ
);

-- Fichiers joints au projet
CREATE TABLE project_files (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id  UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    uploader_id UUID NOT NULL REFERENCES users(id),
    file_url    TEXT NOT NULL,
    file_name   VARCHAR(255),
    file_type   VARCHAR(100),
    file_size_kb INT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Devis / Propositions des freelances
CREATE TABLE quotes (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id      UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    freelance_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount_xof      NUMERIC(12,2) NOT NULL,
    duration_days   INT,
    cover_letter    TEXT,
    status          quote_status NOT NULL DEFAULT 'pending',
    accepted_at     TIMESTAMPTZ,
    refused_at      TIMESTAMPTZ,
    withdrawn_at    TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (project_id, freelance_id)
);

-- FK croisées projects ↔ quotes
ALTER TABLE projects
    ADD CONSTRAINT fk_projects_selected_quote
    FOREIGN KEY (selected_quote_id) REFERENCES quotes(id);

-- Contrats
CREATE TABLE contracts (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id      UUID NOT NULL REFERENCES projects(id),
    quote_id        UUID NOT NULL REFERENCES quotes(id),
    client_id       UUID NOT NULL REFERENCES users(id),
    freelance_id    UUID NOT NULL REFERENCES users(id),
    amount_xof      NUMERIC(12,2) NOT NULL,
    commission_rate NUMERIC(5,2) NOT NULL,             -- % platform au moment de la signature
    commission_xof  NUMERIC(12,2) NOT NULL,
    freelance_net_xof NUMERIC(12,2) NOT NULL,
    start_date      DATE,
    end_date        DATE,
    terms_text      TEXT,                              -- contenu CGU au moment de la signature
    status          contract_status NOT NULL DEFAULT 'draft',
    client_signed_at   TIMESTAMPTZ,
    freelance_signed_at TIMESTAMPTZ,
    completed_at    TIMESTAMPTZ,
    cancelled_at    TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Jalons / Milestones (optionnel mais utile dès v1)
CREATE TABLE milestones (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    contract_id     UUID NOT NULL REFERENCES contracts(id) ON DELETE CASCADE,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    amount_xof      NUMERIC(12,2),
    due_date        DATE,
    delivered_at    TIMESTAMPTZ,
    validated_at    TIMESTAMPTZ,
    sort_order      INT DEFAULT 0
);


-- ============================================================
-- 5. PAIEMENTS & ESCROW AVEC GENIUS PAY
-- ============================================================

-- Table principale des paiements (intégration Genius Pay)
CREATE TABLE payments (
    id                       UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    payer_id                 UUID NOT NULL REFERENCES users(id),
    payee_id                 UUID REFERENCES users(id),     -- NULL pour abonnements
    contract_id              UUID REFERENCES contracts(id),
    subscription_id          UUID REFERENCES freelance_subscriptions(id),
    type                     transaction_type NOT NULL,
    
    -- Genius Pay specific
    genius_pay_transaction_id VARCHAR(255) UNIQUE,          -- ID transaction chez Genius Pay
    genius_pay_status        genius_pay_status DEFAULT 'PENDING',
    payment_method           VARCHAR(50),                   -- 'orange_money', 'mtn_momo', 'wave', 'card', 'ussd'
    payment_channel          payment_channel,               -- MOBILE_MONEY, CARD, BANK_TRANSFER, USSD
    operator_id              payment_operator,              -- ORANGE, MTN, MOOV, WAVE, CARD
    customer_phone           VARCHAR(30),
    customer_email           VARCHAR(255),
    
    -- Montants
    gross_amount_xof         NUMERIC(12,2) NOT NULL,
    commission_xof           NUMERIC(12,2) DEFAULT 0,
    net_amount_xof           NUMERIC(12,2) NOT NULL,
    currency                 VARCHAR(10) DEFAULT 'XOF',
    
    -- Statut interne
    status                   payment_status NOT NULL DEFAULT 'pending',
    
    -- Références externes
    provider_ref             VARCHAR(255),                  -- Ancienne référence (compatibilité)
    provider_response        JSONB,                         -- Réponse complète de Genius Pay
    
    -- Horodatage
    initiated_at             TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    confirmed_at             TIMESTAMPTZ,
    failed_at                TIMESTAMPTZ,
    refunded_at              TIMESTAMPTZ,
    notes                    TEXT
);

-- Table des webhooks Genius Pay (traçabilité)
CREATE TABLE genius_pay_webhooks (
    id                   UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    transaction_id       VARCHAR(255) NOT NULL,             -- ID Genius Pay
    event_type           VARCHAR(100) NOT NULL,             -- payment.success, payment.failed, etc.
    raw_payload          JSONB NOT NULL,                    -- Payload brut reçu
    processed_at         TIMESTAMPTZ,
    processed_by         VARCHAR(255),                      -- 'webhook', 'cron', 'manual'
    status               VARCHAR(50) DEFAULT 'received',    -- received, processing, processed, failed
    error_message        TEXT,
    created_at           TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Table de synchronisation des paiements (cron job)
CREATE TABLE payment_sync_log (
    id                   UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    sync_date            DATE NOT NULL,
    start_time           TIMESTAMPTZ NOT NULL,
    end_time             TIMESTAMPTZ,
    total_checked        INT DEFAULT 0,
    total_updated        INT DEFAULT 0,
    total_failed         INT DEFAULT 0,
    status               VARCHAR(50) DEFAULT 'running',
    error_details        TEXT,
    created_at           TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Ajouter FK subscription → payment
ALTER TABLE freelance_subscriptions
    ADD CONSTRAINT fk_sub_payment
    FOREIGN KEY (payment_id) REFERENCES payments(id);

-- Escrow (séquestre)
CREATE TABLE escrows (
    id                   UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    contract_id          UUID NOT NULL REFERENCES contracts(id) UNIQUE,
    payment_id           UUID NOT NULL REFERENCES payments(id),
    amount_xof           NUMERIC(12,2) NOT NULL,
    status               escrow_status NOT NULL DEFAULT 'holding',
    held_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    release_requested_at TIMESTAMPTZ,
    released_at          TIMESTAMPTZ,
    refunded_at          TIMESTAMPTZ,
    dispute_id           UUID                               -- FK ajoutée après disputes
);

-- Factures PDF
CREATE TABLE invoices (
    id                   UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    payment_id           UUID NOT NULL REFERENCES payments(id) UNIQUE,
    invoice_number       VARCHAR(50) NOT NULL UNIQUE,       -- ex : INV-2025-000001
    issued_to_id         UUID NOT NULL REFERENCES users(id),
    issued_at            TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    pdf_url              TEXT,
    total_xof            NUMERIC(12,2) NOT NULL,
    tax_xof              NUMERIC(12,2) DEFAULT 0,
    notes                TEXT
);

-- Séquence pour numéro de facture
CREATE SEQUENCE invoice_seq START 1;

-- Balance/Wallet plateforme (pour les freelances : fonds disponibles au retrait)
CREATE TABLE wallets (
    user_id              UUID PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    available_xof        NUMERIC(15,2) DEFAULT 0,
    pending_xof          NUMERIC(15,2) DEFAULT 0,
    total_earned_xof     NUMERIC(15,2) DEFAULT 0,
    updated_at           TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Historique wallet
CREATE TABLE wallet_transactions (
    id                   UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    wallet_id            UUID NOT NULL REFERENCES wallets(user_id),
    payment_id           UUID REFERENCES payments(id),
    amount_xof           NUMERIC(12,2) NOT NULL,
    direction            VARCHAR(10) NOT NULL CHECK (direction IN ('credit', 'debit')),
    balance_after_xof    NUMERIC(15,2) NOT NULL,
    description          TEXT,
    created_at           TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Demandes de retrait (adapté pour Genius Pay)
CREATE TABLE withdrawal_requests (
    id                       UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    freelance_id             UUID NOT NULL REFERENCES users(id),
    amount_xof               NUMERIC(12,2) NOT NULL,
    withdrawal_method        withdrawal_method NOT NULL,
    phone_number             VARCHAR(30),
    bank_account             JSONB,                         -- IBAN, SWIFT, bank_name pour virement
    genius_pay_transfer_id   VARCHAR(255),                  -- ID du transfert Genius Pay
    transfer_status          VARCHAR(50) DEFAULT 'pending',
    status                   VARCHAR(30) DEFAULT 'pending',
    processed_at             TIMESTAMPTZ,
    processed_by             UUID REFERENCES users(id),
    notes                    TEXT,
    created_at               TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- 6. MESSAGERIE
-- ============================================================

CREATE TABLE conversations (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    project_id      UUID REFERENCES projects(id),
    contract_id     UUID REFERENCES contracts(id),
    client_id       UUID NOT NULL REFERENCES users(id),
    freelance_id    UUID NOT NULL REFERENCES users(id),
    last_message_at TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (project_id, client_id, freelance_id)
);

CREATE TABLE messages (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    conversation_id UUID NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    sender_id       UUID NOT NULL REFERENCES users(id),
    content         TEXT,
    status          message_status NOT NULL DEFAULT 'sent',
    delivered_at    TIMESTAMPTZ,
    read_at         TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Fichiers partagés dans les messages
CREATE TABLE message_files (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    message_id      UUID NOT NULL REFERENCES messages(id) ON DELETE CASCADE,
    file_url        TEXT NOT NULL,
    file_name       VARCHAR(255),
    file_type       VARCHAR(100),
    file_size_kb    INT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- 7. NOTIFICATIONS
-- ============================================================

CREATE TABLE notifications (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id         UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type            notif_type NOT NULL,
    title           VARCHAR(200) NOT NULL,
    body            TEXT,
    data            JSONB,                             -- contexte (project_id, message_id…)
    is_read         BOOLEAN DEFAULT FALSE,
    read_at         TIMESTAMPTZ,
    sent_email      BOOLEAN DEFAULT FALSE,
    sent_push       BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- 8. AVIS & RÉPUTATION
-- ============================================================

CREATE TABLE reviews (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    contract_id     UUID NOT NULL REFERENCES contracts(id) UNIQUE,
    reviewer_id     UUID NOT NULL REFERENCES users(id),   -- client
    reviewed_id     UUID NOT NULL REFERENCES users(id),   -- freelance
    rating          SMALLINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    -- Critères détaillés
    rating_quality  SMALLINT CHECK (rating_quality BETWEEN 1 AND 5),
    rating_delay    SMALLINT CHECK (rating_delay BETWEEN 1 AND 5),
    rating_communication SMALLINT CHECK (rating_communication BETWEEN 1 AND 5),
    comment         TEXT,
    is_public       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Réponse du freelance à un avis
CREATE TABLE review_replies (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    review_id   UUID NOT NULL REFERENCES reviews(id) UNIQUE,
    author_id   UUID NOT NULL REFERENCES users(id),
    content     TEXT NOT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- ============================================================
-- 9. SIGNALEMENTS & LITIGES
-- ============================================================

CREATE TABLE reports (
    id                  UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    reporter_id         UUID NOT NULL REFERENCES users(id),
    reported_user_id    UUID REFERENCES users(id),
    reported_project_id UUID REFERENCES projects(id),
    type                report_type NOT NULL,
    description         TEXT NOT NULL,
    status              report_status NOT NULL DEFAULT 'open',
    reviewer_id         UUID REFERENCES users(id),
    reviewed_at         TIMESTAMPTZ,
    resolution_note     TEXT,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE disputes (
    id                  UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    contract_id         UUID NOT NULL REFERENCES contracts(id),
    opened_by           UUID NOT NULL REFERENCES users(id),
    status              dispute_status NOT NULL DEFAULT 'open',
    reason              TEXT NOT NULL,
    resolution_note     TEXT,
    resolved_by         UUID REFERENCES users(id),
    resolved_at         TIMESTAMPTZ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

ALTER TABLE escrows
    ADD CONSTRAINT fk_escrow_dispute
    FOREIGN KEY (dispute_id) REFERENCES disputes(id);


-- ============================================================
-- 10. BOOSTS & PUBLICITÉ
-- ============================================================

CREATE TABLE boosts (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id         UUID NOT NULL REFERENCES users(id),
    target          boost_target NOT NULL,
    target_id       UUID NOT NULL,                     -- freelance_id ou project_id
    duration        boost_duration NOT NULL,
    price_xof       NUMERIC(10,2) NOT NULL,
    payment_id      UUID REFERENCES payments(id),
    starts_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    ends_at         TIMESTAMPTZ NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Badge vérifié (achat annuel)
CREATE TABLE verified_badges (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    freelance_id    UUID NOT NULL REFERENCES users(id) UNIQUE,
    payment_id      UUID REFERENCES payments(id),
    price_xof       NUMERIC(10,2) NOT NULL,
    granted_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    expires_at      TIMESTAMPTZ NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE
);


-- ============================================================
-- 11. ADMINISTRATION & BACK-OFFICE
-- ============================================================

-- Log des actions admin
CREATE TABLE admin_logs (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    admin_id        UUID NOT NULL REFERENCES users(id),
    action          VARCHAR(150) NOT NULL,
    target_table    VARCHAR(100),
    target_id       UUID,
    old_value       JSONB,
    new_value       JSONB,
    ip_address      INET,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Paramètres globaux de la plateforme
CREATE TABLE platform_settings (
    key             VARCHAR(100) PRIMARY KEY,
    value           TEXT NOT NULL,
    description     TEXT,
    updated_by      UUID REFERENCES users(id),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ============================================================
-- 12. DONNÉES INITIALES — Catégories, Plans & Settings
-- ============================================================

INSERT INTO platform_settings (key, value, description) VALUES
    ('commission_rate',             '10',     'Taux de commission en % sur chaque transaction'),
    ('commission_rate_max',         '15',     'Taux max possible'),
    ('min_project_budget',          '10000',  'Budget minimum projet en FCFA'),
    ('max_proposal_per_plan',       '{"starter":5,"pro":20,"expert":999}', 'Propositions max par plan'),
    ('boost_price_profile_7d',      '2000',   'Prix boost profil 7 jours (FCFA)'),
    ('boost_price_profile_30d',     '5000',   'Prix boost profil 30 jours (FCFA)'),
    ('boost_price_project_7d',      '3000',   'Prix boost projet 7 jours (FCFA)'),
    ('boost_price_project_30d',     '8000',   'Prix boost projet 30 jours (FCFA)'),
    ('badge_verified_price',        '10000',  'Prix badge vérifié annuel (FCFA)'),
    ('escrow_release_delay_hours',  '48',     'Délai en heures avant déblocage automatique'),
    ('maintenance_mode',            'false',  'Activer le mode maintenance'),
    -- Genius Pay settings
    ('genius_pay_api_key',          '',       'Clé API Genius Pay'),
    ('genius_pay_site_id',          '',       'Site ID Genius Pay'),
    ('genius_pay_webhook_secret',   '',       'Secret pour webhook Genius Pay'),
    ('genius_pay_mode',             'test',   'Mode test ou production (test/production)'),
    ('genius_pay_timeout_seconds',  '120',    'Timeout paiement Genius Pay en secondes'),
    ('genius_pay_cron_interval',    '10',     'Intervalle (minutes) pour cron synchro paiements'),
    ('payment_callback_url',        '',       'URL de callback pour Genius Pay');

INSERT INTO job_categories (slug, name, sort_order) VALUES
    ('dev',           'Développement & Tech',         1),
    ('design',        'Design & Graphisme',           2),
    ('marketing',     'Marketing & Communication',    3),
    ('redaction',     'Rédaction & Traduction',       4),
    ('video',         'Vidéo & Animation',            5),
    ('audio',         'Audio & Musique',              6),
    ('formation',     'Formation & Coaching',         7),
    ('juridique',     'Juridique & Conseil',          8),
    ('comptabilite',  'Comptabilité & Finance',       9),
    ('autre',         'Autre',                        10);

-- Sous-catégories Dev
INSERT INTO job_categories (slug, name, parent_id, sort_order) VALUES
    ('dev-web',       'Développement Web',      (SELECT id FROM job_categories WHERE slug='dev'), 1),
    ('dev-mobile',    'Développement Mobile',   (SELECT id FROM job_categories WHERE slug='dev'), 2),
    ('dev-data',      'Data & IA',              (SELECT id FROM job_categories WHERE slug='dev'), 3),
    ('dev-devops',    'DevOps & Cloud',         (SELECT id FROM job_categories WHERE slug='dev'), 4);

INSERT INTO subscription_plans_config (plan, name_fr, price_xof, max_proposals, featured_slots, has_badge) VALUES
    ('starter', 'Starter', 5000,  5,   0, FALSE),
    ('pro',     'Pro',     10000, 20,  2, TRUE),
    ('expert',  'Expert',  20000, NULL, 5, TRUE);


-- ============================================================
-- 13. INDEX DE PERFORMANCE
-- ============================================================

-- Utilisateurs
CREATE INDEX idx_users_email        ON users(email);
CREATE INDEX idx_users_role         ON users(role);
CREATE INDEX idx_users_status       ON users(status);
CREATE INDEX idx_users_deleted_at   ON users(deleted_at) WHERE deleted_at IS NULL;

-- Profils freelance
CREATE INDEX idx_freelance_category     ON freelance_profiles(category_id);
CREATE INDEX idx_freelance_available    ON freelance_profiles(is_available) WHERE is_available = TRUE;
CREATE INDEX idx_freelance_verified     ON freelance_profiles(is_verified);
CREATE INDEX idx_freelance_rating       ON freelance_profiles(average_rating DESC);
CREATE INDEX idx_freelance_rate         ON freelance_profiles(daily_rate_xof);

-- Projets
CREATE INDEX idx_projects_client    ON projects(client_id);
CREATE INDEX idx_projects_status    ON projects(status);
CREATE INDEX idx_projects_category  ON projects(category_id);
CREATE INDEX idx_projects_featured  ON projects(is_featured) WHERE is_featured = TRUE;
CREATE INDEX idx_projects_created   ON projects(created_at DESC);
CREATE INDEX idx_projects_deleted   ON projects(deleted_at) WHERE deleted_at IS NULL;

-- Devis
CREATE INDEX idx_quotes_project     ON quotes(project_id);
CREATE INDEX idx_quotes_freelance   ON quotes(freelance_id);
CREATE INDEX idx_quotes_status      ON quotes(status);

-- Paiements (Genius Pay)
CREATE INDEX idx_payments_payer          ON payments(payer_id);
CREATE INDEX idx_payments_payee          ON payments(payee_id);
CREATE INDEX idx_payments_status         ON payments(status);
CREATE INDEX idx_payments_contract       ON payments(contract_id);
CREATE INDEX idx_payments_type           ON payments(type);
CREATE INDEX idx_payments_initiated      ON payments(initiated_at DESC);
CREATE INDEX idx_payments_genius_transaction ON payments(genius_pay_transaction_id);
CREATE INDEX idx_payments_payment_method ON payments(payment_method);
CREATE INDEX idx_payments_payment_channel ON payments(payment_channel);

-- Webhooks Genius Pay
CREATE INDEX idx_genius_webhook_transaction ON genius_pay_webhooks(transaction_id);
CREATE INDEX idx_genius_webhook_status      ON genius_pay_webhooks(status);
CREATE INDEX idx_genius_webhook_created     ON genius_pay_webhooks(created_at DESC);

-- Sync log
CREATE INDEX idx_payment_sync_date ON payment_sync_log(sync_date DESC);

-- Messages
CREATE INDEX idx_messages_conv      ON messages(conversation_id);
CREATE INDEX idx_messages_sender    ON messages(sender_id);
CREATE INDEX idx_messages_created   ON messages(created_at DESC);
CREATE INDEX idx_conv_project       ON conversations(project_id);

-- Notifications
CREATE INDEX idx_notifs_user        ON notifications(user_id);
CREATE INDEX idx_notifs_unread      ON notifications(user_id) WHERE is_read = FALSE;
CREATE INDEX idx_notifs_created     ON notifications(created_at DESC);

-- Avis
CREATE INDEX idx_reviews_reviewed   ON reviews(reviewed_id);
CREATE INDEX idx_reviews_rating     ON reviews(rating);

-- Boosts actifs
CREATE INDEX idx_boosts_active      ON boosts(target_id, ends_at) WHERE is_active = TRUE;

-- Recherche full-text freelance (nom + bio)
CREATE INDEX idx_freelance_fts ON profiles USING GIN (
    to_tsvector('french', unaccent(coalesce(first_name,'') || ' ' || coalesce(last_name,'') || ' ' || coalesce(bio,'')))
);

-- Recherche full-text projets
CREATE INDEX idx_projects_fts ON projects USING GIN (
    to_tsvector('french', unaccent(title || ' ' || coalesce(description,'')))
);


-- ============================================================
-- 14. FONCTIONS & TRIGGERS UTILES
-- ============================================================

-- Mise à jour automatique de updated_at
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER trg_profiles_updated_at
    BEFORE UPDATE ON profiles
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER trg_freelance_updated_at
    BEFORE UPDATE ON freelance_profiles
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER trg_projects_updated_at
    BEFORE UPDATE ON projects
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

CREATE TRIGGER trg_quotes_updated_at
    BEFORE UPDATE ON quotes
    FOR EACH ROW EXECUTE FUNCTION set_updated_at();

-- Recalcul automatique de la note moyenne après un avis
CREATE OR REPLACE FUNCTION update_freelance_rating()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE freelance_profiles
    SET
        average_rating = (
            SELECT ROUND(AVG(rating)::NUMERIC, 2)
            FROM reviews
            WHERE reviewed_id = NEW.reviewed_id
        ),
        total_reviews = (
            SELECT COUNT(*) FROM reviews WHERE reviewed_id = NEW.reviewed_id
        )
    WHERE user_id = NEW.reviewed_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_freelance_rating
    AFTER INSERT OR UPDATE ON reviews
    FOR EACH ROW EXECUTE FUNCTION update_freelance_rating();

-- Incrémenter le compteur de devis sur le projet
CREATE OR REPLACE FUNCTION update_project_quotes_count()
RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        UPDATE projects
        SET quotes_count = quotes_count + 1
        WHERE id = NEW.project_id;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE projects
        SET quotes_count = quotes_count - 1
        WHERE id = OLD.project_id;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_project_quotes_count_insert
    AFTER INSERT ON quotes
    FOR EACH ROW EXECUTE FUNCTION update_project_quotes_count();

CREATE TRIGGER trg_project_quotes_count_delete
    AFTER DELETE ON quotes
    FOR EACH ROW EXECUTE FUNCTION update_project_quotes_count();

-- Générer un numéro de facture séquentiel
CREATE OR REPLACE FUNCTION generate_invoice_number()
RETURNS TRIGGER AS $$
BEGIN
    NEW.invoice_number = 'INV-' || TO_CHAR(NOW(), 'YYYY') || '-' || LPAD(nextval('invoice_seq')::TEXT, 6, '0');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_invoice_number
    BEFORE INSERT ON invoices
    FOR EACH ROW WHEN (NEW.invoice_number IS NULL OR NEW.invoice_number = '')
    EXECUTE FUNCTION generate_invoice_number();

-- Mettre à jour la date du dernier message dans la conversation
CREATE OR REPLACE FUNCTION update_conversation_last_message()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE conversations 
    SET last_message_at = NEW.created_at 
    WHERE id = NEW.conversation_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_conversation_last_message
    AFTER INSERT ON messages
    FOR EACH ROW EXECUTE FUNCTION update_conversation_last_message();

-- Mettre à jour le wallet après un paiement libéré
CREATE OR REPLACE FUNCTION update_wallet_on_payment_released()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'released' AND OLD.status != 'released' THEN
        -- Créditer le wallet du payee (freelance)
        INSERT INTO wallets (user_id, available_xof, pending_xof, total_earned_xof)
        VALUES (NEW.payee_id, NEW.net_amount_xof, 0, NEW.net_amount_xof)
        ON CONFLICT (user_id) DO UPDATE
        SET 
            available_xof = wallets.available_xof + NEW.net_amount_xof,
            total_earned_xof = wallets.total_earned_xof + NEW.net_amount_xof,
            updated_at = NOW();
        
        -- Journaliser la transaction
        INSERT INTO wallet_transactions (wallet_id, payment_id, amount_xof, direction, balance_after_xof, description)
        SELECT 
            w.user_id, 
            NEW.id, 
            NEW.net_amount_xof, 
            'credit',
            w.available_xof + NEW.net_amount_xof,
            'Paiement libéré - ' || NEW.type::text
        FROM wallets w
        WHERE w.user_id = NEW.payee_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_wallet_on_payment_released
    AFTER UPDATE ON payments
    FOR EACH ROW
    EXECUTE FUNCTION update_wallet_on_payment_released();


-- ============================================================
-- 15. VUES UTILES
-- ============================================================

-- Vue freelance enrichie (pour la recherche / listing)
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
    fp.hourly_rate_xof,
    fp.average_rating,
    fp.total_reviews,
    fp.missions_completed,
    fp.is_available,
    fp.is_verified,
    jc.name  AS category_name,
    jc.slug  AS category_slug,
    -- Boost actif ?
    EXISTS (
        SELECT 1 FROM boosts b
        WHERE b.target_id = u.id AND b.target = 'profile'
          AND b.is_active AND b.ends_at > NOW()
    ) AS is_boosted
FROM users u
JOIN profiles p           ON p.user_id = u.id
JOIN freelance_profiles fp ON fp.user_id = u.id
LEFT JOIN job_categories jc ON jc.id = fp.category_id
WHERE u.role = 'freelance'
  AND u.status = 'active'
  AND u.deleted_at IS NULL;

-- Vue tableau de bord admin
CREATE OR REPLACE VIEW v_admin_dashboard AS
SELECT
    (SELECT COUNT(*) FROM users WHERE role = 'freelance' AND status = 'active') AS active_freelances,
    (SELECT COUNT(*) FROM users WHERE role = 'client'    AND status = 'active') AS active_clients,
    (SELECT COUNT(*) FROM projects WHERE status = 'open')                       AS open_projects,
    (SELECT COUNT(*) FROM projects WHERE status = 'in_progress')                AS projects_in_progress,
    (SELECT COUNT(*) FROM contracts WHERE status = 'completed')                 AS completed_missions,
    (SELECT COALESCE(SUM(commission_xof), 0) FROM payments WHERE status = 'released') AS total_commissions_xof,
    (SELECT COUNT(*) FROM verifications WHERE status = 'pending')               AS pending_verifications,
    (SELECT COUNT(*) FROM disputes WHERE status IN ('open','under_review'))     AS open_disputes,
    (SELECT COUNT(*) FROM reports WHERE status = 'open')                        AS open_reports;

-- Revenu mensuel par source
CREATE OR REPLACE VIEW v_monthly_revenue AS
SELECT
    DATE_TRUNC('month', initiated_at) AS month,
    type,
    COUNT(*)                          AS nb_transactions,
    SUM(commission_xof)               AS commissions_xof,
    SUM(gross_amount_xof)             AS gross_xof
FROM payments
WHERE status IN ('released', 'confirmed')
GROUP BY 1, 2
ORDER BY 1 DESC, 2;

-- Vue des paiements Genius Pay (pour monitoring)
CREATE OR REPLACE VIEW v_genius_pay_monitoring AS
SELECT
    p.id,
    p.genius_pay_transaction_id,
    p.genius_pay_status,
    p.payment_method,
    p.payment_channel,
    p.operator_id,
    p.gross_amount_xof,
    p.status AS internal_status,
    p.initiated_at,
    p.confirmed_at,
    CASE 
        WHEN p.confirmed_at IS NULL AND p.initiated_at < NOW() - INTERVAL '1 hour' THEN 'TIMEOUT'
        WHEN p.genius_pay_status = 'SUCCESS' AND p.status != 'released' THEN 'DESYNC'
        ELSE 'OK'
    END AS alert_status
FROM payments p
WHERE p.genius_pay_transaction_id IS NOT NULL;

-- ============================================================
-- FIN DU SCHÉMA
-- ============================================================