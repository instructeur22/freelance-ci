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
        'name', 'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function freelanceProfiles(): BelongsToMany
    {
        return $this->belongsToMany(FreelanceProfile::class, 'freelance_skills', 'skill_id', 'freelance_profile_id')
            ->withPivot('proficiency_level');
    }
}
