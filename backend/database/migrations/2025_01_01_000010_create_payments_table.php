<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS payment_status CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE payment_status AS ENUM ('pending', 'held', 'released', 'refunded', 'failed', 'cancelled')");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS payment_channel CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE payment_channel AS ENUM ('MOBILE_MONEY', 'CARD', 'BANK_TRANSFER', 'USSD')");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS payment_operator CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE payment_operator AS ENUM ('ORANGE', 'MTN', 'MOOV', 'WAVE', 'CARD', 'UNKNOWN')");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS transaction_type CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE transaction_type AS ENUM ('mission', 'subscription', 'boost_profile', 'boost_project', 'badge_verified', 'ad', 'refund')");
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->string('status', 20)->default('pending')->comment('payment_status');
            $table->string('channel', 50)->nullable()->comment('payment_channel');
            $table->string('operator', 50)->nullable()->comment('payment_operator');
            $table->string('transaction_type', 50)->comment('transaction_type');
            $table->string('reference', 191)->unique()->nullable();
            $table->string('description', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('genius_pay_webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_type', 100);
            $table->json('payload');
            $table->string('signature', 500)->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_sync_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('action', 100);
            $table->string('status', 50);
            $table->text('request_data')->nullable();
            $table->text('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_sync_log');
        Schema::dropIfExists('genius_pay_webhooks');
        Schema::dropIfExists('payments');
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS transaction_type CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS payment_operator CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS payment_channel CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS payment_status CASCADE");
        }
    }
};
