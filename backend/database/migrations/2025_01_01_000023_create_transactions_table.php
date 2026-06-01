<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->comment('transaction_type');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('XOF');
            $table->text('description')->nullable();
            $table->string('payment_channel', 50)->nullable()->comment('payment_channel');
            $table->string('payment_operator', 50)->nullable()->comment('payment_operator');
            $table->string('operator_status', 50)->nullable();
            $table->string('operator_transaction_id', 191)->nullable();
            $table->string('operator_reference', 191)->nullable();
            $table->string('payment_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
