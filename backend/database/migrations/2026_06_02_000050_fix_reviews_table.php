<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->smallInteger('rating_quality')->nullable()->after('rating');
            $table->smallInteger('rating_delay')->nullable()->after('rating_quality');
            $table->smallInteger('rating_communication')->nullable()->after('rating_delay');
            $table->boolean('is_public')->default(true)->after('is_flagged');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE reviews ADD CONSTRAINT reviews_contract_id_unique UNIQUE (contract_id)");
        }
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['rating_quality', 'rating_delay', 'rating_communication', 'is_public']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE reviews DROP CONSTRAINT IF EXISTS reviews_contract_id_unique");
        }
    }
};
