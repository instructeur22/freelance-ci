<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 2)->nullable()->after('platform_fee');
            $table->decimal('commission_xof', 12, 2)->nullable()->after('commission_rate');
            $table->timestamp('completed_at')->nullable()->after('freelance_signed_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->text('terms_text')->nullable()->after('cancelled_at');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE contracts ALTER COLUMN quote_id SET NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['commission_rate', 'commission_xof', 'completed_at', 'cancelled_at', 'terms_text']);
        });
    }
};
