<?php

namespace App\Models;
use App\Enums\GenderType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasUuids;
    protected $table = 'profiles';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'display_name', 'avatar_url',
        'gender', 'city', 'country', 'bio', 'website_url', 'linkedin_url', 'github_url',
    ];

    protected function casts(): array
    {
        return [
            'gender' => GenderType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
