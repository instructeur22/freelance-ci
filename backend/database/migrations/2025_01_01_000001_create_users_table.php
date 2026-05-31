<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP TYPE IF EXISTS user_role CASCADE");
        DB::statement("CREATE TYPE user_role AS ENUM ('client', 'freelance', 'admin')");
        DB::statement("DROP TYPE IF EXISTS account_status CASCADE");
        DB::statement("CREATE TYPE account_status AS ENUM ('pending', 'active', 'suspended', 'banned')");

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->comment('user_role');
            $table->string('status', 20)->default('pending')->comment('account_status');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->string('locale', 5)->default('fr');
            $table->rememberToken();
            $table->timestamps();
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('provider_id', 191);
            $table->string('provider_token', 1000)->nullable();
            $table->string('provider_refresh_token', 1000)->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_id']);
        });

        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_name', 100)->nullable();
            $table->string('device_type', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_tokens');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('users');
        DB::statement("DROP TYPE IF EXISTS account_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS user_role CASCADE");
    }
};
