<?php

namespace App\Models;
use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanConfig extends Model
{
    use HasUuids;
    protected $table = 'subscription_plans_config';

    protected $fillable = [
        'plan', 'name', 'description', 'price_monthly', 'price_yearly',
        'max_projects', 'max_quotes_per_month',
        'has_verified_badge', 'has_boost_option',
        'features', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'plan' => SubscriptionPlan::class,
            'has_verified_badge' => 'boolean',
            'has_boost_option' => 'boolean',
            'is_active' => 'boolean',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'features' => 'array',
        ];
    }
}
