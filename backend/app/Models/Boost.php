<?php

namespace App\Models;

use App\Enums\BoostDuration;
use App\Enums\BoostTarget;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boost extends Model
{
    protected $table = 'boosts';

    protected $fillable = [
        'user_id', 'payment_id', 'target', 'target_id',
        'duration', 'is_active', 'started_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'target' => BoostTarget::class,
            'duration' => BoostDuration::class,
            'is_active' => 'boolean',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
