<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 255)->nullable();
            $table->string('color', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('parent_id')->nullable();
            $table->timestamps();
        });

        Schema::table('job_categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('job_categories')->nullOnDelete();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->foreignUuid('category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('freelance_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('freelance_profile_id')->constrained('freelance_profiles')->cascadeOnDelete();
            $table->foreignUuid('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('proficiency_level', 50)->nullable();
            $table->integer('years_experience')->nullable();
            $table->timestamps();
            $table->unique(['freelance_profile_id', 'skill_id']);
        });

        Schema::create('freelance_languages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('freelance_profile_id')->constrained('freelance_profiles')->cascadeOnDelete();
            $table->string('language', 50);
            $table->string('proficiency_level', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelance_languages');
        Schema::dropIfExists('freelance_skills');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('job_categories');
    }
};
