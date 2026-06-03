<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelanceSubscription extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'freelance_subscriptions';

    protected $fillable = [
        'freelance_profile_id', 'plan_id', 'status',
        'started_at', 'ends_at', 'trial_ends_at',
        'billing_cycle', 'amount_paid', 'payment_reference',
        'auto_renew', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'amount_paid' => 'decimal:2',
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'auto_renew' => 'boolean',
        ];
    }

    public function freelanceProfile(): BelongsTo
    {
        return $this->belongsTo(FreelanceProfile::class, 'freelance_profile_id');
    }

    public function planConfig(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlanConfig::class, 'plan_id');
    }
}
