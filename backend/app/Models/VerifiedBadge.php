<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifiedBadge extends Model
{
    use HasUuids;
    protected $table = 'verified_badges';

    protected $fillable = [
        'freelance_profile_id', 'verification_id', 'badge_type',
        'is_active', 'granted_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function freelanceProfile(): BelongsTo
    {
        return $this->belongsTo(FreelanceProfile::class, 'freelance_profile_id');
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class, 'verification_id');
    }
}
