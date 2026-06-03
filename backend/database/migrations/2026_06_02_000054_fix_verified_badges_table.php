<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verified_badges', function (Blueprint $table) {
            $table->foreignUuid('payment_id')->nullable()->after('verification_id');
            $table->decimal('price_xof', 12, 2)->nullable()->after('payment_id');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE verified_badges ADD CONSTRAINT verified_badges_freelance_profile_id_unique UNIQUE (freelance_profile_id)");
        }
    }

    public function down(): void
    {
        Schema::table('verified_badges', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn(['payment_id', 'price_xof']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE verified_badges DROP CONSTRAINT IF EXISTS verified_badges_freelance_profile_id_unique");
        }
    }
};
