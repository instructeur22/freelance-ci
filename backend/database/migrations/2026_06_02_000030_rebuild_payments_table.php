<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignUuid('payer_id')->nullable()->after('id');
            $table->foreignUuid('payee_id')->nullable()->after('payer_id');
            $table->string('genius_pay_transaction_id', 255)->nullable()->unique()->after('payee_id');
            $table->string('genius_pay_status', 20)->nullable()->after('genius_pay_transaction_id');
            $table->string('payment_channel', 20)->nullable()->after('genius_pay_status');
            $table->string('operator_id', 20)->nullable()->after('payment_channel');
            $table->string('customer_phone', 30)->nullable()->after('operator_id');
            $table->string('customer_email', 191)->nullable()->after('customer_phone');
            $table->decimal('gross_amount_xof', 15, 2)->nullable()->after('customer_email');
            $table->decimal('commission_xof', 12, 2)->nullable()->after('gross_amount_xof');
            $table->timestamp('initiated_at')->nullable()->after('paid_at');
            $table->timestamp('confirmed_at')->nullable()->after('initiated_at');
            $table->timestamp('failed_at')->nullable()->after('confirmed_at');
            $table->timestamp('refunded_at')->nullable()->after('failed_at');
            $table->jsonb('provider_response')->nullable()->after('refunded_at');

            $table->renameColumn('user_id', 'legacy_user_id');
            $table->renameColumn('net_amount', 'net_amount_xof');
            $table->renameColumn('channel', 'legacy_channel');
            $table->renameColumn('operator', 'legacy_operator');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('legacy_user_id', 'user_id');
            $table->renameColumn('net_amount_xof', 'net_amount');
            $table->renameColumn('legacy_channel', 'channel');
            $table->renameColumn('legacy_operator', 'operator');

            $table->dropColumn([
                'payer_id', 'payee_id', 'genius_pay_transaction_id', 'genius_pay_status',
                'payment_channel', 'operator_id', 'customer_phone', 'customer_email',
                'gross_amount_xof', 'commission_xof',
                'initiated_at', 'confirmed_at', 'failed_at', 'refunded_at',
                'provider_response',
            ]);
        });
    }
};
