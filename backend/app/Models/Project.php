<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'projects';

    protected $fillable = [
        'client_id', 'category_id', 'title', 'description',
        'status', 'budget_min', 'budget_max', 'currency',
        'experience_level', 'duration_days', 'required_skills',
        'project_type', 'is_featured', 'featured_until', 'is_urgent', 'is_remote',
        'location', 'selected_quote_id', 'quotes_count', 'views_count',
        'published_at', 'deadline_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'is_featured' => 'boolean',
            'is_urgent' => 'boolean',
            'is_remote' => 'boolean',
            'required_skills' => 'array',
            'featured_until' => 'datetime',
            'deadline_at' => 'datetime',
            'published_at' => 'datetime',
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
