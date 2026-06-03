<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('featured_until')->nullable()->after('is_featured');
            $table->foreignUuid('selected_quote_id')->nullable()->after('featured_until');
        });

        if (Schema::hasColumn('projects', 'required_skills')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->json('required_skills')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['selected_quote_id']);
            $table->dropColumn(['featured_until', 'selected_quote_id']);
        });
    }
};
