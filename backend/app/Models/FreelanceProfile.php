<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FreelanceProfile extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'freelance_profiles';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'professional_title', 'tagline', 'experience_level', 'years_experience',
        'education_level', 'hourly_rate_min', 'hourly_rate_max', 'daily_rate_xof',
        'currency', 'is_available', 'availability_note',
        'total_projects_completed', 'total_projects_in_progress',
        'average_rating', 'total_reviews', 'total_earnings', 'success_rate',
        'response_rate', 'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'average_rating' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'success_rate' => 'decimal:2',
            'response_rate' => 'decimal:2',
            'daily_rate_xof' => 'decimal:2',
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'freelance_skills', 'freelance_id', 'skill_id')
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

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class, 'freelance_profile_id');
    }

    public function boosts(): HasMany
    {
        return $this->hasMany(Boost::class, 'freelance_profile_id');
    }
}
