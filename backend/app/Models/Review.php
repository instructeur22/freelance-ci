<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'reviews';

    protected $fillable = [
        'contract_id', 'reviewer_id', 'reviewee_id',
        'rating', 'rating_quality', 'rating_delay', 'rating_communication',
        'comment', 'criteria_ratings', 'is_flagged', 'is_public',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'rating_quality' => 'integer',
            'rating_delay' => 'integer',
            'rating_communication' => 'integer',
            'criteria_ratings' => 'array',
            'is_flagged' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function reply(): HasOne
    {
        return $this->hasOne(ReviewReply::class, 'review_id');
    }
}
