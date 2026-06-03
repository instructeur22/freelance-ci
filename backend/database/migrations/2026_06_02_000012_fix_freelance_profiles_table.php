<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelance_profiles', function (Blueprint $table) {
            $table->string('tagline', 255)->nullable()->after('professional_title');
            $table->decimal('daily_rate_xof', 12, 2)->nullable()->after('hourly_rate_max');
            $table->string('availability_note', 255)->nullable()->after('is_available');
            $table->decimal('response_rate', 5, 2)->nullable()->after('success_rate');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE freelance_profiles DROP CONSTRAINT IF EXISTS freelance_profiles_user_id_unique");
            DB::statement("ALTER TABLE freelance_profiles ADD CONSTRAINT freelance_profiles_user_id_unique UNIQUE (user_id)");
        }
    }

    public function down(): void
    {
        Schema::table('freelance_profiles', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'daily_rate_xof', 'availability_note', 'response_rate']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE freelance_profiles DROP CONSTRAINT IF EXISTS freelance_profiles_user_id_unique");
        }
    }
};
