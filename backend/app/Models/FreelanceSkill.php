<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FreelanceSkill extends Pivot
{
    use HasUuids;
    protected $table = 'freelance_skills';

    protected $fillable = [
        'freelance_id', 'skill_id', 'level',
    ];
}
