<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('genius_pay_transfer_id', 255)->nullable()->after('net_amount');
            $table->jsonb('bank_account')->nullable()->after('genius_pay_transfer_id');
            $table->string('phone_number', 30)->nullable()->after('bank_account');
            $table->renameColumn('method', 'withdrawal_method');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE withdrawal_requests ADD CONSTRAINT withdrawal_requests_method_check CHECK (withdrawal_method IN ('orange_money', 'mtn_momo', 'wave', 'bank_transfer'))");
        }
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->renameColumn('withdrawal_method', 'method');
            $table->dropColumn(['genius_pay_transfer_id', 'bank_account', 'phone_number']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE withdrawal_requests DROP CONSTRAINT IF EXISTS withdrawal_requests_method_check");
        }
    }
};
