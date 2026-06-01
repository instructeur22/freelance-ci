<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('referred_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('referral_code_id')->constrained('referral_codes')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique('referred_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_codes');
    }
};
