<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignUuid('issued_to_id')->nullable()->after('payment_id');
            $table->text('pdf_url')->nullable()->after('issued_to_id');
            $table->decimal('tax_xof', 12, 2)->default(0)->after('pdf_url');
            $table->renameColumn('amount', 'total_xof');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('total_xof', 'amount');
            $table->dropForeign(['issued_to_id']);
            $table->dropColumn(['issued_to_id', 'pdf_url', 'tax_xof']);
        });
    }
};
