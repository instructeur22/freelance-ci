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
        'plan', 'name_fr', 'price_xof', 'max_proposals',
        'featured_slots', 'has_badge', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'plan' => SubscriptionPlan::class,
            'has_badge' => 'boolean',
            'is_active' => 'boolean',
            'price_xof' => 'decimal:2',
        ];
    }
}
