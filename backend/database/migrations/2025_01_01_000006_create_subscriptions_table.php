<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP TYPE IF EXISTS subscription_plan CASCADE");
        DB::statement("CREATE TYPE subscription_plan AS ENUM ('starter', 'pro', 'expert')");
        DB::statement("DROP TYPE IF EXISTS subscription_status_type CASCADE");
        DB::statement("CREATE TYPE subscription_status_type AS ENUM ('active', 'cancelled', 'expired', 'trial')");

        Schema::create('subscription_plans_config', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('plan', 50)->unique()->comment('subscription_plan');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2);
            $table->integer('max_projects')->nullable();
            $table->integer('max_quotes_per_month')->nullable();
            $table->boolean('has_verified_badge')->default(false);
            $table->boolean('has_boost_option')->default(false);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('freelance_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('freelance_profile_id')->constrained('freelance_profiles')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('subscription_plans_config')->cascadeOnDelete();
            $table->string('status', 20)->default('trial')->comment('subscription_status_type');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('billing_cycle', 20)->default('monthly');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_reference', 191)->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelance_subscriptions');
        Schema::dropIfExists('subscription_plans_config');
        DB::statement("DROP TYPE IF EXISTS subscription_status_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS subscription_plan CASCADE");
    }
};
