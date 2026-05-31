<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Trigger: set_updated_at
        DB::statement("
            CREATE OR REPLACE FUNCTION set_updated_at()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");

        // Apply set_updated_at trigger to all tables with updated_at
        $tables = [
            'users', 'profiles', 'client_profiles', 'freelance_profiles',
            'job_categories', 'skills', 'freelance_skills', 'freelance_languages',
            'portfolio_items', 'portfolio_files',
            'verifications', 'subscription_plans_config', 'freelance_subscriptions',
            'projects', 'project_files', 'quotes',
            'contracts', 'milestones', 'payments', 'genius_pay_webhooks', 'payment_sync_log',
            'escrows', 'invoices', 'wallets', 'wallet_transactions', 'withdrawal_requests',
            'conversations', 'messages', 'message_files',
            'notifications', 'reviews', 'review_replies',
            'reports', 'disputes', 'boosts', 'verified_badges',
            'admin_logs', 'platform_settings',
        ];

        foreach ($tables as $table) {
            DB::statement("
                CREATE TRIGGER set_updated_at_$table
                BEFORE UPDATE ON $table
                FOR EACH ROW
                EXECUTE FUNCTION set_updated_at()
            ");
        }

        // Trigger: update_freelance_rating
        DB::statement("
            CREATE OR REPLACE FUNCTION update_freelance_rating()
            RETURNS TRIGGER AS $$
            BEGIN
                UPDATE freelance_profiles
                SET
                    average_rating = (
                        SELECT COALESCE(AVG(r.rating), 0)
                        FROM reviews r
                        WHERE r.reviewee_id = freelance_profiles.user_id
                        AND r.deleted_at IS NULL
                    ),
                    total_reviews = (
                        SELECT COUNT(*)
                        FROM reviews r
                        WHERE r.reviewee_id = freelance_profiles.user_id
                        AND r.deleted_at IS NULL
                    )
                WHERE user_id = NEW.reviewee_id;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");

        DB::statement("
            CREATE TRIGGER trigger_update_freelance_rating
            AFTER INSERT OR UPDATE ON reviews
            FOR EACH ROW
            EXECUTE FUNCTION update_freelance_rating()
        ");

        // Trigger: update_project_quotes_count
        DB::statement("
            CREATE OR REPLACE FUNCTION update_project_quotes_count()
            RETURNS TRIGGER AS $$
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    UPDATE projects SET quotes_count = quotes_count + 1 WHERE id = NEW.project_id;
                ELSIF TG_OP = 'DELETE' THEN
                    UPDATE projects SET quotes_count = GREATEST(quotes_count - 1, 0) WHERE id = OLD.project_id;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");

        DB::statement("
            CREATE TRIGGER trigger_update_project_quotes_count
            AFTER INSERT OR DELETE ON quotes
            FOR EACH ROW
            EXECUTE FUNCTION update_project_quotes_count()
        ");

        // Trigger: generate_invoice_number
        DB::statement("
            CREATE OR REPLACE FUNCTION generate_invoice_number()
            RETURNS TRIGGER AS $$
            DECLARE
                seq_num BIGINT;
            BEGIN
                seq_num := NEXTVAL('invoice_seq');
                NEW.invoice_number := 'INV-' || TO_CHAR(NEW.issue_date, 'YYYYMM') || '-' || LPAD(seq_num::TEXT, 6, '0');
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");

        DB::statement("
            CREATE TRIGGER trigger_generate_invoice_number
            BEFORE INSERT ON invoices
            FOR EACH ROW
            EXECUTE FUNCTION generate_invoice_number()
        ");

        // Trigger: update_conversation_last_message
        DB::statement("
            CREATE OR REPLACE FUNCTION update_conversation_last_message()
            RETURNS TRIGGER AS $$
            BEGIN
                UPDATE conversations
                SET last_message_at = NEW.created_at
                WHERE id = NEW.conversation_id;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");

        DB::statement("
            CREATE TRIGGER trigger_update_conversation_last_message
            AFTER INSERT ON messages
            FOR EACH ROW
            EXECUTE FUNCTION update_conversation_last_message()
        ");

        // Trigger: update_wallet_on_payment_released
        DB::statement("
            CREATE OR REPLACE FUNCTION update_wallet_on_payment_released()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.status = 'released' AND OLD.status = 'held' THEN
                    UPDATE wallets
                    SET
                        balance = balance + NEW.net_amount,
                        pending_balance = pending_balance - NEW.net_amount,
                        total_earned = total_earned + NEW.net_amount
                    WHERE user_id = (
                        SELECT freelance_id FROM contracts WHERE id = NEW.contract_id
                    );
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");

        DB::statement("
            CREATE TRIGGER trigger_update_wallet_on_payment_released
            AFTER UPDATE ON payments
            FOR EACH ROW
            EXECUTE FUNCTION update_wallet_on_payment_released()
        ");

        // View: v_freelance_listing
        DB::statement("
            CREATE OR REPLACE VIEW v_freelance_listing AS
            SELECT
                u.id AS user_id,
                u.first_name,
                u.last_name,
                u.avatar_url,
                fp.professional_title,
                fp.experience_level,
                fp.years_experience,
                fp.hourly_rate_min,
                fp.hourly_rate_max,
                fp.currency,
                fp.average_rating,
                fp.total_reviews,
                fp.total_projects_completed,
                fp.success_rate,
                fp.is_available,
                fp.last_active_at,
                p.country,
                p.city,
                p.bio,
                COALESCE(
                    (SELECT json_agg(json_build_object(
                        'id', s.id,
                        'name', s.name,
                        'slug', s.slug
                    )) FROM freelance_skills fs
                    JOIN skills s ON s.id = fs.skill_id
                    WHERE fs.freelance_profile_id = fp.id),
                    '[]'::json
                ) AS skills,
                COALESCE(
                    (SELECT json_agg(json_build_object(
                        'id', vb.id,
                        'badge_type', vb.badge_type
                    )) FROM verified_badges vb
                    WHERE vb.freelance_profile_id = fp.id AND vb.is_active = true),
                    '[]'::json
                ) AS badges
            FROM users u
            JOIN freelance_profiles fp ON fp.user_id = u.id
            JOIN profiles p ON p.user_id = u.id
            WHERE u.role = 'freelance'
            AND u.deleted_at IS NULL
        ");

        // View: v_admin_dashboard
        DB::statement("
            CREATE OR REPLACE VIEW v_admin_dashboard AS
            SELECT
                (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) AS total_users,
                (SELECT COUNT(*) FROM users WHERE role = 'freelance' AND deleted_at IS NULL) AS total_freelancers,
                (SELECT COUNT(*) FROM users WHERE role = 'client' AND deleted_at IS NULL) AS total_clients,
                (SELECT COUNT(*) FROM projects WHERE deleted_at IS NULL) AS total_projects,
                (SELECT COUNT(*) FROM projects WHERE status = 'open') AS open_projects,
                (SELECT COUNT(*) FROM projects WHERE status = 'in_progress') AS in_progress_projects,
                (SELECT COUNT(*) FROM contracts WHERE status = 'signed') AS active_contracts,
                (SELECT COUNT(*) FROM contracts WHERE status = 'completed') AS completed_contracts,
                (SELECT COALESCE(SUM(total_amount), 0) FROM contracts WHERE status = 'completed') AS total_revenue,
                (SELECT COALESCE(SUM(platform_fee), 0) FROM contracts WHERE status = 'completed') AS total_platform_fees,
                (SELECT COUNT(*) FROM disputes WHERE status IN ('open', 'under_review')) AS pending_disputes,
                (SELECT COUNT(*) FROM verifications WHERE status = 'pending') AS pending_verifications,
                (SELECT COUNT(*) FROM withdrawal_requests WHERE status = 'pending') AS pending_withdrawals,
                (SELECT COUNT(*) FROM boosts WHERE is_active = true) AS active_boosts
        ");

        // View: v_monthly_revenue
        DB::statement("
            CREATE OR REPLACE VIEW v_monthly_revenue AS
            SELECT
                DATE_TRUNC('month', p.paid_at) AS month,
                COUNT(DISTINCT p.id) AS total_transactions,
                COALESCE(SUM(p.amount), 0) AS total_amount,
                COALESCE(SUM(p.platform_fee), 0) AS total_fees,
                COALESCE(SUM(p.net_amount), 0) AS total_net
            FROM payments p
            WHERE p.status = 'released'
            AND p.paid_at IS NOT NULL
            GROUP BY DATE_TRUNC('month', p.paid_at)
            ORDER BY month DESC
        ");

        // View: v_genius_pay_monitoring
        DB::statement("
            CREATE OR REPLACE VIEW v_genius_pay_monitoring AS
            SELECT
                gpw.id,
                gpw.event_type,
                gpw.is_processed,
                gpw.error_message,
                gpw.created_at,
                gpw.processed_at,
                CASE
                    WHEN gpw.is_processed = true THEN 'processed'
                    WHEN gpw.created_at < NOW() - INTERVAL '1 hour' THEN 'stuck'
                    ELSE 'pending'
                END AS status
            FROM genius_pay_webhooks gpw
            ORDER BY gpw.created_at DESC
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_genius_pay_monitoring");
        DB::statement("DROP VIEW IF EXISTS v_monthly_revenue");
        DB::statement("DROP VIEW IF EXISTS v_admin_dashboard");
        DB::statement("DROP VIEW IF EXISTS v_freelance_listing");

        DB::statement("DROP TRIGGER IF EXISTS trigger_update_wallet_on_payment_released ON payments");
        DB::statement("DROP FUNCTION IF EXISTS update_wallet_on_payment_released");
        DB::statement("DROP TRIGGER IF EXISTS trigger_update_conversation_last_message ON messages");
        DB::statement("DROP FUNCTION IF EXISTS update_conversation_last_message");
        DB::statement("DROP TRIGGER IF EXISTS trigger_generate_invoice_number ON invoices");
        DB::statement("DROP FUNCTION IF EXISTS generate_invoice_number");
        DB::statement("DROP TRIGGER IF EXISTS trigger_update_project_quotes_count ON quotes");
        DB::statement("DROP FUNCTION IF EXISTS update_project_quotes_count");
        DB::statement("DROP TRIGGER IF EXISTS trigger_update_freelance_rating ON reviews");
        DB::statement("DROP FUNCTION IF EXISTS update_freelance_rating");

        $tables = [
            'users', 'profiles', 'client_profiles', 'freelance_profiles',
            'job_categories', 'skills', 'freelance_skills', 'freelance_languages',
            'portfolio_items', 'portfolio_files',
            'verifications', 'subscription_plans_config', 'freelance_subscriptions',
            'projects', 'project_files', 'quotes',
            'contracts', 'milestones', 'payments', 'genius_pay_webhooks', 'payment_sync_log',
            'escrows', 'invoices', 'wallets', 'wallet_transactions', 'withdrawal_requests',
            'conversations', 'messages', 'message_files',
            'notifications', 'reviews', 'review_replies',
            'reports', 'disputes', 'boosts', 'verified_badges',
            'admin_logs', 'platform_settings',
        ];

        foreach ($tables as $table) {
            DB::statement("DROP TRIGGER IF EXISTS set_updated_at_$table ON $table");
        }

        DB::statement("DROP FUNCTION IF EXISTS set_updated_at");
    }
};
