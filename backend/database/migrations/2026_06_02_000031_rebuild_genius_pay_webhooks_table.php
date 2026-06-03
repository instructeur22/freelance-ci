<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('genius_pay_webhooks', function (Blueprint $table) {
            $table->string('transaction_id', 255)->nullable()->after('event_type');
            $table->renameColumn('payload', 'raw_payload');
            $table->string('processed_by', 255)->nullable()->after('processed_at');

            if (Schema::hasColumn('genius_pay_webhooks', 'raw_payload')) {
                $table->json('raw_payload')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('genius_pay_webhooks', function (Blueprint $table) {
            $table->renameColumn('raw_payload', 'payload');
            $table->dropColumn(['transaction_id', 'processed_by']);
        });
    }
};
