<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP TYPE IF EXISTS gender_type CASCADE");
        DB::statement("CREATE TYPE gender_type AS ENUM ('homme', 'femme', 'autre', 'non_precise')");

        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bio', 2000)->nullable();
            $table->string('title', 200)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('gender', 20)->nullable()->comment('gender_type');
            $table->date('birth_date')->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('github_url', 500)->nullable();
            $table->string('phone_secondary', 30)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('client_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name', 200)->nullable();
            $table->string('company_website', 500)->nullable();
            $table->string('company_size', 50)->nullable();
            $table->string('industry', 100)->nullable();
            $table->integer('total_projects_posted')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('freelance_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('professional_title', 200)->nullable();
            $table->string('experience_level', 50)->nullable();
            $table->integer('years_experience')->default(0);
            $table->string('education_level', 100)->nullable();
            $table->string('hourly_rate_min', 20)->nullable();
            $table->string('hourly_rate_max', 20)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->boolean('is_available')->default(true);
            $table->integer('total_projects_completed')->default(0);
            $table->integer('total_projects_in_progress')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelance_profiles');
        Schema::dropIfExists('client_profiles');
        Schema::dropIfExists('profiles');
        DB::statement("DROP TYPE IF EXISTS gender_type CASCADE");
    }
};
