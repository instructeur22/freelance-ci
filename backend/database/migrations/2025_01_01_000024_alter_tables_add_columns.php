<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignUuid('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
        });

        Schema::table('escrows', function (Blueprint $table) {
            $table->foreignUuid('payment_id')->nullable()->change();
        });

        Schema::table('milestones', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->comment('milestone_status');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('validated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn(['status', 'delivered_at', 'validated_at']);
        });

        Schema::table('escrows', function (Blueprint $table) {
            $table->foreignUuid('payment_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
    }
};
