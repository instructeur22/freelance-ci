<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelance_languages', function (Blueprint $table) {
            $table->renameColumn('freelance_profile_id', 'freelance_id');
        });
    }

    public function down(): void
    {
        Schema::table('freelance_languages', function (Blueprint $table) {
            $table->renameColumn('freelance_id', 'freelance_profile_id');
        });
    }
};
