<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('cover_letter', 2000)->nullable()->after('proposal');
            $table->timestamp('accepted_at')->nullable()->after('responded_at');
            $table->timestamp('refused_at')->nullable()->after('accepted_at');
            $table->timestamp('withdrawn_at')->nullable()->after('refused_at');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE quotes ADD CONSTRAINT quotes_project_freelance_unique UNIQUE (project_id, freelance_id)");
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['cover_letter', 'accepted_at', 'refused_at', 'withdrawn_at']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE quotes DROP CONSTRAINT IF EXISTS quotes_project_freelance_unique");
        }
    }
};
