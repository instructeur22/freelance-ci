<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignUuid('payment_id')->nullable()->after('wallet_id');
            $table->string('direction', 10)->nullable()->after('payment_id');
            $table->renameColumn('amount', 'amount_xof');
            $table->renameColumn('balance_before', 'balance_before_xof');
            $table->renameColumn('balance_after', 'balance_after_xof');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->renameColumn('amount_xof', 'amount');
            $table->renameColumn('balance_before_xof', 'balance_before');
            $table->renameColumn('balance_after_xof', 'balance_after');
            $table->dropForeign(['payment_id']);
            $table->dropColumn(['payment_id', 'direction']);
        });
    }
};
