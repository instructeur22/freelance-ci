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

        // v_freelance_listing — updated with new column names
        DB::statement("
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
                fp.hourly_rate_min AS hourly_rate_min_xof,
                fp.hourly_rate_max AS hourly_rate_max_xof,
                fp.average_rating,
                fp.total_reviews,
                fp.total_projects_completed AS missions_completed,
                fp.is_available,
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
              AND u.deleted_at IS NULL
        ");

        // v_admin_dashboard — updated with new schema
        DB::statement("
            CREATE OR REPLACE VIEW v_admin_dashboard AS
            SELECT
                (SELECT COUNT(*) FROM users WHERE role = 'freelance' AND status = 'active')   AS active_freelances,
                (SELECT COUNT(*) FROM users WHERE role = 'client'    AND status = 'active')   AS active_clients,
                (SELECT COUNT(*) FROM projects WHERE status = 'open')                         AS open_projects,
                (SELECT COUNT(*) FROM projects WHERE status = 'in_progress')                  AS projects_in_progress,
                (SELECT COUNT(*) FROM contracts WHERE status = 'completed')                   AS completed_missions,
                (SELECT COALESCE(SUM(commission_xof), 0) FROM payments WHERE status = 'released') AS total_commissions_xof,
                (SELECT COUNT(*) FROM verifications WHERE status = 'pending')                 AS pending_verifications,
                (SELECT COUNT(*) FROM disputes WHERE status IN ('open', 'under_review'))      AS open_disputes,
                (SELECT COUNT(*) FROM reports WHERE status = 'open')                          AS open_reports,
                (SELECT COUNT(*) FROM withdrawal_requests WHERE status = 'pending')           AS pending_withdrawals
        ");

        // v_monthly_revenue — updated with new column names
        DB::statement("
            CREATE OR REPLACE VIEW v_monthly_revenue AS
            SELECT
                DATE_TRUNC('month', initiated_at) AS month,
                COUNT(*)                          AS nb_transactions,
                SUM(COALESCE(commission_xof, 0))  AS commissions_xof,
                SUM(COALESCE(gross_amount_xof, 0)) AS gross_xof,
                SUM(COALESCE(net_amount_xof, 0))   AS net_xof
            FROM payments
            WHERE status IN ('released', 'confirmed')
              AND initiated_at IS NOT NULL
            GROUP BY DATE_TRUNC('month', initiated_at)
            ORDER BY month DESC
        ");

        // v_genius_pay_monitoring — updated with new schema
        DB::statement("
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
            WHERE p.genius_pay_transaction_id IS NOT NULL
        ");

        // v_wallet_summary — new view
        DB::statement("
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
            GROUP BY u.id, p.first_name, p.last_name,
                     w.available_xof, w.pending_xof,
                     w.total_earned_xof, w.total_withdrawn_xof
        ");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("DROP VIEW IF EXISTS v_freelance_listing");
        DB::statement("DROP VIEW IF EXISTS v_admin_dashboard");
        DB::statement("DROP VIEW IF EXISTS v_monthly_revenue");
        DB::statement("DROP VIEW IF EXISTS v_genius_pay_monitoring");
        DB::statement("DROP VIEW IF EXISTS v_wallet_summary");
    }
};
