<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use HasUuids;
    protected $table = 'skills';

    protected $fillable = [
        'name', 'category_id', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function freelancers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'freelance_skills', 'skill_id', 'freelance_id')
            ->withPivot('level');
    }
}
