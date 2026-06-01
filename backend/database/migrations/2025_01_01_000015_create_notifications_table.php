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
            DB::statement("DROP TYPE IF EXISTS notification_type CASCADE");
        }
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("CREATE TYPE notification_type AS ENUM ('message', 'offer', 'payment', 'project', 'review', 'system', 'alert')");
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->comment('notification_type');
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("DROP TYPE IF EXISTS notification_type CASCADE");
        }
    }
};
