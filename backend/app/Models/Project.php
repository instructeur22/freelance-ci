<?php

namespace App\Models;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'projects';

    protected $fillable = [
        'client_id', 'category_id', 'title', 'description',
        'budget_min_xof', 'budget_max_xof', 'deadline', 'skills_required',
        'status', 'is_featured', 'featured_until', 'views_count',
        'quotes_count', 'selected_quote_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'is_featured' => 'boolean',
            'skills_required' => 'array',
            'deadline' => 'date',
            'featured_until' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function selectedQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'selected_quote_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class, 'project_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'project_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'project_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'project_id');
    }
}
