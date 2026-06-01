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
            DB::statement("DROP TYPE IF EXISTS escrow_status CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE escrow_status AS ENUM ('holding', 'released', 'refunded', 'disputed')");
        }

        Schema::create('escrows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->string('status', 20)->default('holding')->comment('escrow_status');
            $table->decimal('amount', 12, 2);
            $table->decimal('held_amount', 12, 2)->default(0);
            $table->decimal('released_amount', 12, 2)->default(0);
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->timestamp('held_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS escrow_status CASCADE");
        }
    }
};
