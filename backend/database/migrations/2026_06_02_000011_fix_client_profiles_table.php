<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE client_profiles DROP CONSTRAINT IF EXISTS client_profiles_user_id_unique");
            DB::statement("ALTER TABLE client_profiles ADD CONSTRAINT client_profiles_user_id_unique UNIQUE (user_id)");
        }

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->string('company_sector', 100)->nullable()->after('company_size');
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn('company_sector');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE client_profiles DROP CONSTRAINT IF EXISTS client_profiles_user_id_unique");
        }
    }
};
