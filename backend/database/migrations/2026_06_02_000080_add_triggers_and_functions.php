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

        // Credit wallet on escrow release
        DB::statement("
            CREATE OR REPLACE FUNCTION credit_wallet_on_escrow_release()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.status = 'released' AND OLD.status != 'released' THEN
                    INSERT INTO wallets (user_id, available_xof, pending_xof, total_earned_xof, created_at, updated_at)
                    SELECT
                        c.freelance_id,
                        NEW.net_amount,
                        0,
                        NEW.net_amount,
                        NOW(),
                        NOW()
                    FROM payments p
                    JOIN contracts c ON c.id = p.contract_id
                    WHERE p.id = NEW.payment_id
                    ON CONFLICT (user_id) DO UPDATE
                    SET
                        available_xof    = wallets.available_xof + EXCLUDED.available_xof,
                        pending_xof      = GREATEST(0, wallets.pending_xof - EXCLUDED.available_xof),
                        total_earned_xof = wallets.total_earned_xof + EXCLUDED.total_earned_xof,
                        updated_at       = NOW();
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql
        ");

        DB::statement("
            DROP TRIGGER IF EXISTS trigger_credit_wallet_on_escrow_release ON escrows
        ");

        DB::statement("
            CREATE TRIGGER trigger_credit_wallet_on_escrow_release
            AFTER UPDATE ON escrows
            FOR EACH ROW
            WHEN (NEW.status = 'released' AND (OLD.status IS NULL OR OLD.status != 'released'))
            EXECUTE FUNCTION credit_wallet_on_escrow_release()
        ");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("DROP TRIGGER IF EXISTS trigger_credit_wallet_on_escrow_release ON escrows");
        DB::statement("DROP FUNCTION IF EXISTS credit_wallet_on_escrow_release");
    }
};
