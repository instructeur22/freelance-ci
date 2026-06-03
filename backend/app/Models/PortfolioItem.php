<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioItem extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'portfolio_items';

    protected $fillable = [
        'freelance_profile_id', 'title', 'description', 'project_url',
        'completed_date', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'completed_date' => 'date',
        ];
    }

    public function freelanceProfile(): BelongsTo
    {
        return $this->belongsTo(FreelanceProfile::class, 'freelance_profile_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PortfolioFile::class, 'portfolio_item_id');
    }
}
