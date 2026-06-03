<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('category_id');
        });

        Schema::table('job_categories', function (Blueprint $table) {
            $table->string('icon_url', 500)->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('job_categories', function (Blueprint $table) {
            $table->dropColumn('icon_url');
        });
    }
};
