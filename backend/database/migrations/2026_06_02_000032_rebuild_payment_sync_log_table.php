<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('payment_sync_log', 'payment_action_log');

        Schema::create('payment_sync_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamp('sync_date');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('total_checked')->default(0);
            $table->integer('total_updated')->default(0);
            $table->integer('total_failed')->default(0);
            $table->string('status', 20)->default('running');
            $table->text('error_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_sync_log');
        Schema::rename('payment_action_log', 'payment_sync_log');
    }
};
