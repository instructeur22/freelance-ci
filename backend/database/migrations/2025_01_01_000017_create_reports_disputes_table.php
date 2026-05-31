<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP TYPE IF EXISTS report_type CASCADE");
        DB::statement("CREATE TYPE report_type AS ENUM ('profil', 'comportement', 'contenu', 'fraude', 'autre')");
        DB::statement("DROP TYPE IF EXISTS report_status CASCADE");
        DB::statement("CREATE TYPE report_status AS ENUM ('open', 'under_review', 'resolved', 'dismissed')");
        DB::statement("DROP TYPE IF EXISTS dispute_status CASCADE");
        DB::statement("CREATE TYPE dispute_status AS ENUM ('open', 'under_review', 'resolved_client', 'resolved_freelance', 'closed')");

        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->comment('report_type');
            $table->string('status', 20)->default('open')->comment('report_status');
            $table->text('description');
            $table->json('evidence')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignUuid('raised_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('open')->comment('dispute_status');
            $table->text('reason');
            $table->json('evidence')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('reports');
        DB::statement("DROP TYPE IF EXISTS dispute_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS report_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS report_type CASCADE");
    }
};
