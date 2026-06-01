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

        $indexes = [
            'idx_users_email' => 'CREATE INDEX IF NOT EXISTS idx_users_email ON users USING btree (email)',
            'idx_users_role' => 'CREATE INDEX IF NOT EXISTS idx_users_role ON users USING btree (role)',
            'idx_users_status' => 'CREATE INDEX IF NOT EXISTS idx_users_status ON users USING btree (status)',
            'idx_users_created_at' => 'CREATE INDEX IF NOT EXISTS idx_users_created_at ON users USING btree (created_at)',
            'idx_social_accounts_user_id' => 'CREATE INDEX IF NOT EXISTS idx_social_accounts_user_id ON social_accounts USING btree (user_id)',
            'idx_auth_tokens_user_id' => 'CREATE INDEX IF NOT EXISTS idx_auth_tokens_user_id ON auth_tokens USING btree (user_id)',
            'idx_profiles_user_id' => 'CREATE INDEX IF NOT EXISTS idx_profiles_user_id ON profiles USING btree (user_id)',
            'idx_client_profiles_user_id' => 'CREATE INDEX IF NOT EXISTS idx_client_profiles_user_id ON client_profiles USING btree (user_id)',
            'idx_freelance_profiles_user_id' => 'CREATE INDEX IF NOT EXISTS idx_freelance_profiles_user_id ON freelance_profiles USING btree (user_id)',
            'idx_freelance_profiles_rating' => 'CREATE INDEX IF NOT EXISTS idx_freelance_profiles_rating ON freelance_profiles USING btree (average_rating DESC)',
            'idx_freelance_profiles_available' => 'CREATE INDEX IF NOT EXISTS idx_freelance_profiles_available ON freelance_profiles USING btree (is_available)',
            'idx_skills_category_id' => 'CREATE INDEX IF NOT EXISTS idx_skills_category_id ON skills USING btree (category_id)',
            'idx_freelance_skills_profile_id' => 'CREATE INDEX IF NOT EXISTS idx_freelance_skills_profile_id ON freelance_skills USING btree (freelance_profile_id)',
            'idx_freelance_skills_skill_id' => 'CREATE INDEX IF NOT EXISTS idx_freelance_skills_skill_id ON freelance_skills USING btree (skill_id)',
            'idx_freelance_languages_profile_id' => 'CREATE INDEX IF NOT EXISTS idx_freelance_languages_profile_id ON freelance_languages USING btree (freelance_profile_id)',
            'idx_portfolio_items_profile_id' => 'CREATE INDEX IF NOT EXISTS idx_portfolio_items_profile_id ON portfolio_items USING btree (freelance_profile_id)',
            'idx_portfolio_files_item_id' => 'CREATE INDEX IF NOT EXISTS idx_portfolio_files_item_id ON portfolio_files USING btree (portfolio_item_id)',
            'idx_verifications_user_id' => 'CREATE INDEX IF NOT EXISTS idx_verifications_user_id ON verifications USING btree (user_id)',
            'idx_verifications_status' => 'CREATE INDEX IF NOT EXISTS idx_verifications_status ON verifications USING btree (status)',
            'idx_verifications_type' => 'CREATE INDEX IF NOT EXISTS idx_verifications_type ON verifications USING btree (type)',
            'idx_freelance_subscriptions_profile_id' => 'CREATE INDEX IF NOT EXISTS idx_freelance_subscriptions_profile_id ON freelance_subscriptions USING btree (freelance_profile_id)',
            'idx_freelance_subscriptions_status' => 'CREATE INDEX IF NOT EXISTS idx_freelance_subscriptions_status ON freelance_subscriptions USING btree (status)',
            'idx_projects_client_id' => 'CREATE INDEX IF NOT EXISTS idx_projects_client_id ON projects USING btree (client_id)',
            'idx_projects_category_id' => 'CREATE INDEX IF NOT EXISTS idx_projects_category_id ON projects USING btree (category_id)',
            'idx_projects_status' => 'CREATE INDEX IF NOT EXISTS idx_projects_status ON projects USING btree (status)',
            'idx_projects_created_at' => 'CREATE INDEX IF NOT EXISTS idx_projects_created_at ON projects USING btree (created_at DESC)',
            'idx_projects_published_at' => 'CREATE INDEX IF NOT EXISTS idx_projects_published_at ON projects USING btree (published_at DESC)',
            'idx_projects_status_client' => 'CREATE INDEX IF NOT EXISTS idx_projects_status_client ON projects USING btree (client_id, status)',
            'idx_projects_project_files' => 'CREATE INDEX IF NOT EXISTS idx_projects_project_files ON project_files USING btree (project_id)',
            'idx_quotes_project_id' => 'CREATE INDEX IF NOT EXISTS idx_quotes_project_id ON quotes USING btree (project_id)',
            'idx_quotes_freelance_id' => 'CREATE INDEX IF NOT EXISTS idx_quotes_freelance_id ON quotes USING btree (freelance_id)',
            'idx_quotes_status' => 'CREATE INDEX IF NOT EXISTS idx_quotes_status ON quotes USING btree (status)',
            'idx_quotes_project_status' => 'CREATE INDEX IF NOT EXISTS idx_quotes_project_status ON quotes USING btree (project_id, status)',
            'idx_contracts_project_id' => 'CREATE INDEX IF NOT EXISTS idx_contracts_project_id ON contracts USING btree (project_id)',
            'idx_contracts_client_id' => 'CREATE INDEX IF NOT EXISTS idx_contracts_client_id ON contracts USING btree (client_id)',
            'idx_contracts_freelance_id' => 'CREATE INDEX IF NOT EXISTS idx_contracts_freelance_id ON contracts USING btree (freelance_id)',
            'idx_contracts_status' => 'CREATE INDEX IF NOT EXISTS idx_contracts_status ON contracts USING btree (status)',
            'idx_milestones_contract_id' => 'CREATE INDEX IF NOT EXISTS idx_milestones_contract_id ON milestones USING btree (contract_id)',
            'idx_payments_contract_id' => 'CREATE INDEX IF NOT EXISTS idx_payments_contract_id ON payments USING btree (contract_id)',
            'idx_payments_user_id' => 'CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments USING btree (user_id)',
            'idx_payments_status' => 'CREATE INDEX IF NOT EXISTS idx_payments_status ON payments USING btree (status)',
            'idx_payments_reference' => 'CREATE INDEX IF NOT EXISTS idx_payments_reference ON payments USING btree (reference)',
            'idx_payments_created_at' => 'CREATE INDEX IF NOT EXISTS idx_payments_created_at ON payments USING btree (created_at DESC)',
            'idx_payments_transaction_type' => 'CREATE INDEX IF NOT EXISTS idx_payments_transaction_type ON payments USING btree (transaction_type)',
            'idx_genius_pay_webhooks_processed' => 'CREATE INDEX IF NOT EXISTS idx_genius_pay_webhooks_processed ON genius_pay_webhooks USING btree (is_processed)',
            'idx_payment_sync_log_payment_id' => 'CREATE INDEX IF NOT EXISTS idx_payment_sync_log_payment_id ON payment_sync_log USING btree (payment_id)',
            'idx_escrows_payment_id' => 'CREATE INDEX IF NOT EXISTS idx_escrows_payment_id ON escrows USING btree (payment_id)',
            'idx_escrows_contract_id' => 'CREATE INDEX IF NOT EXISTS idx_escrows_contract_id ON escrows USING btree (contract_id)',
            'idx_escrows_status' => 'CREATE INDEX IF NOT EXISTS idx_escrows_status ON escrows USING btree (status)',
            'idx_invoices_contract_id' => 'CREATE INDEX IF NOT EXISTS idx_invoices_contract_id ON invoices USING btree (contract_id)',
            'idx_invoices_payment_id' => 'CREATE INDEX IF NOT EXISTS idx_invoices_payment_id ON invoices USING btree (payment_id)',
            'idx_invoices_invoice_number' => 'CREATE INDEX IF NOT EXISTS idx_invoices_invoice_number ON invoices USING btree (invoice_number)',
            'idx_wallets_user_id' => 'CREATE INDEX IF NOT EXISTS idx_wallets_user_id ON wallets USING btree (user_id)',
            'idx_wallet_transactions_wallet_id' => 'CREATE INDEX IF NOT EXISTS idx_wallet_transactions_wallet_id ON wallet_transactions USING btree (wallet_id)',
            'idx_withdrawal_requests_wallet_id' => 'CREATE INDEX IF NOT EXISTS idx_withdrawal_requests_wallet_id ON withdrawal_requests USING btree (wallet_id)',
            'idx_withdrawal_requests_user_id' => 'CREATE INDEX IF NOT EXISTS idx_withdrawal_requests_user_id ON withdrawal_requests USING btree (user_id)',
            'idx_withdrawal_requests_status' => 'CREATE INDEX IF NOT EXISTS idx_withdrawal_requests_status ON withdrawal_requests USING btree (status)',
            'idx_conversations_project_id' => 'CREATE INDEX IF NOT EXISTS idx_conversations_project_id ON conversations USING btree (project_id)',
            'idx_conversations_contract_id' => 'CREATE INDEX IF NOT EXISTS idx_conversations_contract_id ON conversations USING btree (contract_id)',
            'idx_conversation_participants_conversation_id' => 'CREATE INDEX IF NOT EXISTS idx_conversation_participants_conversation_id ON conversation_participants USING btree (conversation_id)',
            'idx_conversation_participants_user_id' => 'CREATE INDEX IF NOT EXISTS idx_conversation_participants_user_id ON conversation_participants USING btree (user_id)',
            'idx_messages_conversation_id' => 'CREATE INDEX IF NOT EXISTS idx_messages_conversation_id ON messages USING btree (conversation_id)',
            'idx_messages_sender_id' => 'CREATE INDEX IF NOT EXISTS idx_messages_sender_id ON messages USING btree (sender_id)',
            'idx_messages_created_at' => 'CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages USING btree (created_at DESC)',
            'idx_message_files_message_id' => 'CREATE INDEX IF NOT EXISTS idx_message_files_message_id ON message_files USING btree (message_id)',
            'idx_notifications_user_id' => 'CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications USING btree (user_id)',
            'idx_notifications_is_read' => 'CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications USING btree (is_read)',
            'idx_notifications_created_at' => 'CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications USING btree (created_at DESC)',
            'idx_reviews_contract_id' => 'CREATE INDEX IF NOT EXISTS idx_reviews_contract_id ON reviews USING btree (contract_id)',
            'idx_reviews_reviewer_id' => 'CREATE INDEX IF NOT EXISTS idx_reviews_reviewer_id ON reviews USING btree (reviewer_id)',
            'idx_reviews_reviewee_id' => 'CREATE INDEX IF NOT EXISTS idx_reviews_reviewee_id ON reviews USING btree (reviewee_id)',
            'idx_review_replies_review_id' => 'CREATE INDEX IF NOT EXISTS idx_review_replies_review_id ON review_replies USING btree (review_id)',
            'idx_reports_reporter_id' => 'CREATE INDEX IF NOT EXISTS idx_reports_reporter_id ON reports USING btree (reporter_id)',
            'idx_reports_reported_user_id' => 'CREATE INDEX IF NOT EXISTS idx_reports_reported_user_id ON reports USING btree (reported_user_id)',
            'idx_reports_status' => 'CREATE INDEX IF NOT EXISTS idx_reports_status ON reports USING btree (status)',
            'idx_disputes_contract_id' => 'CREATE INDEX IF NOT EXISTS idx_disputes_contract_id ON disputes USING btree (contract_id)',
            'idx_disputes_raised_by' => 'CREATE INDEX IF NOT EXISTS idx_disputes_raised_by ON disputes USING btree (raised_by)',
            'idx_disputes_status' => 'CREATE INDEX IF NOT EXISTS idx_disputes_status ON disputes USING btree (status)',
            'idx_boosts_freelance_profile_id' => 'CREATE INDEX IF NOT EXISTS idx_boosts_freelance_profile_id ON boosts USING btree (freelance_profile_id)',
            'idx_boosts_is_active' => 'CREATE INDEX IF NOT EXISTS idx_boosts_is_active ON boosts USING btree (is_active)',
            'idx_verified_badges_freelance_profile_id' => 'CREATE INDEX IF NOT EXISTS idx_verified_badges_freelance_profile_id ON verified_badges USING btree (freelance_profile_id)',
            'idx_admin_logs_admin_id' => 'CREATE INDEX IF NOT EXISTS idx_admin_logs_admin_id ON admin_logs USING btree (admin_id)',
            'idx_admin_logs_created_at' => 'CREATE INDEX IF NOT EXISTS idx_admin_logs_created_at ON admin_logs USING btree (created_at DESC)',
            'idx_platform_settings_key' => 'CREATE INDEX IF NOT EXISTS idx_platform_settings_key ON platform_settings USING btree (key)',
            'idx_platform_settings_group' => 'CREATE INDEX IF NOT EXISTS idx_platform_settings_group ON platform_settings USING btree ("group")',
            'idx_projects_search' => "CREATE INDEX IF NOT EXISTS idx_projects_search ON projects USING gin (to_tsvector('french', coalesce(title, '') || ' ' || coalesce(description, '')))",
            'idx_freelance_profiles_search' => "CREATE INDEX IF NOT EXISTS idx_freelance_profiles_search ON freelance_profiles USING gin (to_tsvector('french', coalesce(professional_title, '')))",
        ];

        foreach ($indexes as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        $indexes = [
            'idx_freelance_profiles_search', 'idx_projects_search',
            'idx_platform_settings_group', 'idx_platform_settings_key',
            'idx_admin_logs_created_at', 'idx_admin_logs_admin_id',
            'idx_verified_badges_freelance_profile_id', 'idx_boosts_is_active', 'idx_boosts_freelance_profile_id',
            'idx_disputes_status', 'idx_disputes_raised_by', 'idx_disputes_contract_id',
            'idx_reports_status', 'idx_reports_reported_user_id', 'idx_reports_reporter_id',
            'idx_review_replies_review_id', 'idx_reviews_reviewee_id', 'idx_reviews_reviewer_id', 'idx_reviews_contract_id',
            'idx_notifications_created_at', 'idx_notifications_is_read', 'idx_notifications_user_id',
            'idx_message_files_message_id', 'idx_messages_created_at', 'idx_messages_sender_id', 'idx_messages_conversation_id',
            'idx_conversation_participants_user_id', 'idx_conversation_participants_conversation_id',
            'idx_conversations_contract_id', 'idx_conversations_project_id',
            'idx_withdrawal_requests_status', 'idx_withdrawal_requests_user_id', 'idx_withdrawal_requests_wallet_id',
            'idx_wallet_transactions_wallet_id', 'idx_wallets_user_id',
            'idx_invoices_invoice_number', 'idx_invoices_payment_id', 'idx_invoices_contract_id',
            'idx_escrows_status', 'idx_escrows_contract_id', 'idx_escrows_payment_id',
            'idx_payment_sync_log_payment_id', 'idx_genius_pay_webhooks_processed',
            'idx_payments_transaction_type', 'idx_payments_created_at', 'idx_payments_reference', 'idx_payments_status', 'idx_payments_user_id', 'idx_payments_contract_id',
            'idx_milestones_contract_id',
            'idx_contracts_status', 'idx_contracts_freelance_id', 'idx_contracts_client_id', 'idx_contracts_project_id',
            'idx_quotes_project_status', 'idx_quotes_status', 'idx_quotes_freelance_id', 'idx_quotes_project_id',
            'idx_projects_project_files', 'idx_projects_status_client', 'idx_projects_published_at', 'idx_projects_created_at', 'idx_projects_status', 'idx_projects_category_id', 'idx_projects_client_id',
            'idx_freelance_subscriptions_status', 'idx_freelance_subscriptions_profile_id',
            'idx_verifications_type', 'idx_verifications_status', 'idx_verifications_user_id',
            'idx_portfolio_files_item_id', 'idx_portfolio_items_profile_id',
            'idx_freelance_languages_profile_id', 'idx_freelance_skills_skill_id', 'idx_freelance_skills_profile_id',
            'idx_skills_category_id',
            'idx_freelance_profiles_available', 'idx_freelance_profiles_rating', 'idx_freelance_profiles_user_id',
            'idx_client_profiles_user_id', 'idx_profiles_user_id',
            'idx_auth_tokens_user_id', 'idx_social_accounts_user_id',
            'idx_users_created_at', 'idx_users_status', 'idx_users_role', 'idx_users_email',
        ];

        foreach ($indexes as $index) {
            DB::statement("DROP INDEX IF EXISTS $index");
        }
    }
};
