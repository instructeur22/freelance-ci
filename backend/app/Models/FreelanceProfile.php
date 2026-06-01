<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FreelanceProfile extends Model
{
    use HasUuids;
    protected $table = 'freelance_profiles';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'professional_title', 'experience_level', 'years_experience',
        'education_level', 'hourly_rate_min', 'hourly_rate_max', 'currency',
        'is_available', 'total_projects_completed', 'total_projects_in_progress',
        'average_rating', 'total_reviews', 'total_earnings', 'success_rate',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'average_rating' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'success_rate' => 'decimal:2',
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'freelance_skills', 'freelance_profile_id', 'skill_id')
            ->withPivot('proficiency_level');
    }

    public function verifiedBadges(): HasMany
    {
        return $this->hasMany(VerifiedBadge::class, 'freelance_profile_id');
    }

    public function activeBadge(): HasMany
    {
        return $this->hasMany(VerifiedBadge::class, 'freelance_profile_id')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function freelanceSubscriptions(): HasMany
    {
        return $this->hasMany(FreelanceSubscription::class, 'freelance_profile_id');
    }
}
