<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('freelance_profile_id')->constrained('freelance_profiles')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('project_url', 500)->nullable();
            $table->date('completed_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('portfolio_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('portfolio_item_id')->constrained('portfolio_items')->cascadeOnDelete();
            $table->string('file_url', 500);
            $table->string('file_type', 50)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_files');
        Schema::dropIfExists('portfolio_items');
    }
};
