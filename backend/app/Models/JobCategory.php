<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCategory extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'job_categories';

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'icon_url', 'color', 'sort_order', 'is_active', 'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(JobCategory::class, 'parent_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class, 'category_id');
    }
}
