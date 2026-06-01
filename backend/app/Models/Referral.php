<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasUuids;
    protected $table = 'referrals';

    protected $fillable = [
        'referrer_id', 'referred_id', 'referral_code_id',
        'status', 'reward_amount', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'reward_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class, 'referral_code_id');
    }
}
