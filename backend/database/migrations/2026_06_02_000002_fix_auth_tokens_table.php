<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->string('token_hash', 64)->nullable()->unique()->after('id');
            $table->string('type', 50)->nullable()->after('token_hash');
            $table->timestamp('used_at')->nullable()->after('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->dropColumn(['token_hash', 'type', 'used_at']);
        });
    }
};
