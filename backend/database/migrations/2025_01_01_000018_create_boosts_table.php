<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP TYPE IF EXISTS boost_target CASCADE");
        DB::statement("CREATE TYPE boost_target AS ENUM ('profile', 'project')");
        DB::statement("DROP TYPE IF EXISTS boost_duration CASCADE");
        DB::statement("CREATE TYPE boost_duration AS ENUM ('7_days', '30_days')");

        Schema::create('boosts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('freelance_profile_id')->constrained('freelance_profiles')->cascadeOnDelete();
            $table->string('target_type', 20)->comment('boost_target');
            $table->foreignUuid('target_id')->nullable()->comment('UUID of project or null for profile');
            $table->string('duration', 20)->comment('boost_duration');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_reference', 191)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('verified_badges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('freelance_profile_id')->constrained('freelance_profiles')->cascadeOnDelete();
            $table->foreignUuid('verification_id')->nullable()->constrained('verifications')->nullOnDelete();
            $table->string('badge_type', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verified_badges');
        Schema::dropIfExists('boosts');
        DB::statement("DROP TYPE IF EXISTS boost_duration CASCADE");
        DB::statement("DROP TYPE IF EXISTS boost_target CASCADE");
    }
};
