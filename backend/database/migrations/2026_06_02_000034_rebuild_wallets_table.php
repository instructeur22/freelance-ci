<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('balance', 'available_xof');
            $table->renameColumn('pending_balance', 'pending_xof');
            $table->renameColumn('total_earned', 'total_earned_xof');
            $table->decimal('total_withdrawn_xof', 15, 2)->default(0)->after('total_earned_xof');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE wallets ADD CONSTRAINT wallets_user_id_unique UNIQUE (user_id)");
        }
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('available_xof', 'balance');
            $table->renameColumn('pending_xof', 'pending_balance');
            $table->renameColumn('total_earned_xof', 'total_earned');
            $table->dropColumn('total_withdrawn_xof');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE wallets DROP CONSTRAINT IF EXISTS wallets_user_id_unique");
        }
    }
};
