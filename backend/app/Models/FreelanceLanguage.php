<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FreelanceLanguage extends Model
{
    use HasUuids;
    protected $table = 'freelance_languages';

    protected $fillable = [
        'freelance_id', 'language', 'proficiency',
    ];
}
