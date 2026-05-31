<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP TYPE IF EXISTS quote_status CASCADE");
        DB::statement("CREATE TYPE quote_status AS ENUM ('pending', 'accepted', 'refused', 'withdrawn')");

        Schema::create('quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('freelance_id')->constrained('users')->cascadeOnDelete();
            $table->text('proposal')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('XOF');
            $table->integer('estimated_duration')->nullable();
            $table->string('status', 20)->default('pending')->comment('quote_status');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
        DB::statement("DROP TYPE IF EXISTS quote_status CASCADE");
    }
};
