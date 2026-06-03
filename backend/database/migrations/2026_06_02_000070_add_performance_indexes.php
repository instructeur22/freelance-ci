<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        // Users
        DB::statement("CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_users_deleted ON users(deleted_at) WHERE deleted_at IS NULL");

        // Freelance profiles
        DB::statement("CREATE INDEX IF NOT EXISTS idx_fp_available ON freelance_profiles(is_available) WHERE is_available = TRUE");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_fp_verified ON freelance_profiles(is_verified)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_fp_rating ON freelance_profiles(average_rating DESC)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_fp_rate ON freelance_profiles(daily_rate_xof)");

        // Projects
        DB::statement("CREATE INDEX IF NOT EXISTS idx_projects_status ON projects(status)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_projects_client ON projects(client_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_projects_category ON projects(category_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_projects_featured ON projects(is_featured) WHERE is_featured = TRUE");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_projects_created ON projects(created_at DESC)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_projects_deleted ON projects(deleted_at) WHERE deleted_at IS NULL");

        // Quotes
        DB::statement("CREATE INDEX IF NOT EXISTS idx_quotes_project ON quotes(project_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_quotes_freelance ON quotes(freelance_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_quotes_status ON quotes(status)");

        // Payments Genius Pay
        DB::statement("CREATE INDEX IF NOT EXISTS idx_payments_payer ON payments(payer_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_payments_payee ON payments(payee_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_payments_contract ON payments(contract_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_payments_genius_id ON payments(genius_pay_transaction_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_payments_initiated ON payments(initiated_at DESC)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_payments_channel ON payments(payment_channel)");

        // Webhooks
        DB::statement("CREATE INDEX IF NOT EXISTS idx_webhook_transaction ON genius_pay_webhooks(transaction_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_webhook_status ON genius_pay_webhooks(status)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_webhook_created ON genius_pay_webhooks(created_at DESC)");

        // Messages
        DB::statement("CREATE INDEX IF NOT EXISTS idx_messages_conv ON messages(conversation_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_messages_sender ON messages(sender_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_messages_created ON messages(created_at DESC)");

        // Notifications
        DB::statement("CREATE INDEX IF NOT EXISTS idx_notifs_unread ON notifications(user_id) WHERE is_read = FALSE");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_notifs_created ON notifications(created_at DESC)");

        // Reviews
        DB::statement("CREATE INDEX IF NOT EXISTS idx_reviews_reviewed ON reviews(reviewee_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_reviews_rating ON reviews(rating)");

        // Active boosts
        DB::statement("CREATE INDEX IF NOT EXISTS idx_boosts_active ON boosts(target_id, ends_at) WHERE is_active = TRUE");

        // Full-text search
        DB::statement("CREATE EXTENSION IF NOT EXISTS unaccent");
        DB::statement("CREATE OR REPLACE FUNCTION public.immutable_unaccent(text) RETURNS text AS \$\$ SELECT public.unaccent(\$1) \$\$ LANGUAGE sql IMMUTABLE PARALLEL SAFE");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_freelance_fts ON profiles USING GIN (to_tsvector('french', immutable_unaccent(coalesce(first_name, '') || ' ' || coalesce(last_name, '') || ' ' || coalesce(bio, ''))))");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_projects_fts ON projects USING GIN (to_tsvector('french', immutable_unaccent(title || ' ' || coalesce(description, ''))))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        $indexes = [
            'idx_users_role', 'idx_users_deleted',
            'idx_fp_available', 'idx_fp_verified', 'idx_fp_rating', 'idx_fp_rate',
            'idx_projects_status', 'idx_projects_client', 'idx_projects_category',
            'idx_projects_featured', 'idx_projects_created', 'idx_projects_deleted',
            'idx_quotes_project', 'idx_quotes_freelance', 'idx_quotes_status',
            'idx_payments_payer', 'idx_payments_payee', 'idx_payments_status',
            'idx_payments_contract', 'idx_payments_genius_id', 'idx_payments_initiated',
            'idx_payments_channel',
            'idx_webhook_transaction', 'idx_webhook_status', 'idx_webhook_created',
            'idx_messages_conv', 'idx_messages_sender', 'idx_messages_created',
            'idx_notifs_unread', 'idx_notifs_created',
            'idx_reviews_reviewed', 'idx_reviews_rating',
            'idx_boosts_active',
            'idx_freelance_fts', 'idx_projects_fts',
        ];

        foreach ($indexes as $index) {
            DB::statement("DROP INDEX IF EXISTS $index");
        }
    }
};
