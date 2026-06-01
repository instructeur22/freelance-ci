<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreelanceLanguage extends Model
{
    use HasUuids;
    protected $table = 'freelance_languages';

    protected $fillable = [
        'freelance_profile_id', 'language', 'proficiency_level',
    ];

    public function freelanceProfile(): BelongsTo
    {
        return $this->belongsTo(FreelanceProfile::class, 'freelance_profile_id');
    }
}
