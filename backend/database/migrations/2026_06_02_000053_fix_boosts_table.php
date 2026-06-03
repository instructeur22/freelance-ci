<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boosts', function (Blueprint $table) {
            $table->foreignUuid('user_id')->nullable()->after('id');
            $table->foreignUuid('payment_id')->nullable()->after('user_id');
            $table->renameColumn('target_type', 'target');
        });
    }

    public function down(): void
    {
        Schema::table('boosts', function (Blueprint $table) {
            $table->renameColumn('target', 'target_type');
            $table->dropForeign(['payment_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'payment_id']);
        });
    }
};
