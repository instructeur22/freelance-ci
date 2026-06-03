<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignUuid('client_id')->nullable()->after('contract_id');
            $table->foreignUuid('freelance_id')->nullable()->after('client_id');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE conversations ADD CONSTRAINT conversations_unique_triple UNIQUE (project_id, client_id, freelance_id)");
        }
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['freelance_id']);
            $table->dropColumn(['client_id', 'freelance_id']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE conversations DROP CONSTRAINT IF EXISTS conversations_unique_triple");
        }
    }
};
