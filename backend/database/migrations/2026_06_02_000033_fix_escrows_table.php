<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('escrows', function (Blueprint $table) {
            $table->timestamp('release_requested_at')->nullable()->after('released_at');
            $table->foreignUuid('dispute_id')->nullable()->after('release_requested_at');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE escrows ADD CONSTRAINT escrows_contract_id_unique UNIQUE (contract_id)");
            DB::statement("ALTER TABLE escrows ALTER COLUMN payment_id SET NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('escrows', function (Blueprint $table) {
            $table->dropForeign(['dispute_id']);
            $table->dropColumn(['release_requested_at', 'dispute_id']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE escrows DROP CONSTRAINT IF EXISTS escrows_contract_id_unique");
        }
    }
};
