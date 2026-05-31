<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelanceProfile extends Model
{
    use HasUuids;
    protected $table = 'freelance_profiles';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'tagline', 'category_id', 'experience_years',
        'daily_rate_xof', 'hourly_rate_xof', 'is_available', 'availability_note',
        'is_verified', 'verified_at', 'average_rating', 'total_reviews',
        'total_earned_xof', 'missions_completed', 'response_rate',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'is_verified' => 'boolean',
            'average_rating' => 'decimal:2',
            'daily_rate_xof' => 'decimal:2',
            'hourly_rate_xof' => 'decimal:2',
            'total_earned_xof' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
}
