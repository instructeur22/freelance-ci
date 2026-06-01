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
            DB::statement("DROP TYPE IF EXISTS verification_type CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE verification_type AS ENUM ('identity', 'portfolio', 'diploma', 'professional')");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS verification_status CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE verification_status AS ENUM ('pending', 'approved', 'rejected')");
        }

        Schema::create('verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->comment('verification_type');
            $table->string('status', 20)->default('pending')->comment('verification_status');
            $table->string('document_url', 500)->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS verification_status CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS verification_type CASCADE");
        }
    }
};
