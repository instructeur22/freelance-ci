<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('milestones', 'is_completed')) {
            Schema::table('milestones', function (Blueprint $table) {
                $table->boolean('is_completed')->default(false)->after('due_date');
            });
        }
    }

    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};
