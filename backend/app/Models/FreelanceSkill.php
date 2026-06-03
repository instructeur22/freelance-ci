<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FreelanceSkill extends Pivot
{
    use HasFactory, HasUuids;
    protected $table = 'freelance_skills';

    protected $fillable = [
        'freelance_id', 'skill_id', 'proficiency_level', 'years_experience',
    ];
}
