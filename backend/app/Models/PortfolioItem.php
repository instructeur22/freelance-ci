<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioItem extends Model
{
    use HasUuids;
    protected $table = 'portfolio_items';

    protected $fillable = [
        'freelance_id', 'title', 'description', 'category_id',
        'cover_url', 'project_url', 'tags', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function freelance(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelance_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PortfolioFile::class, 'item_id');
    }
}
