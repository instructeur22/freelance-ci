<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Enums\BoostDuration;
use App\Enums\BoostTarget;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boost extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'boosts';

    protected $fillable = [
        'freelance_profile_id', 'user_id', 'payment_id', 'target', 'target_id',
        'duration', 'amount_paid', 'payment_reference',
        'is_active', 'started_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'target' => BoostTarget::class,
            'duration' => BoostDuration::class,
            'is_active' => 'boolean',
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function freelanceProfile(): BelongsTo
    {
        return $this->belongsTo(FreelanceProfile::class, 'freelance_profile_id');
    }
}
