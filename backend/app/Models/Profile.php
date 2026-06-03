<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\GenderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'profiles';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'display_name', 'bio', 'title', 'country', 'city', 'address',
        'gender', 'birth_date', 'website_url', 'linkedin_url',
        'github_url', 'phone_secondary', 'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'gender' => GenderType::class,
            'birth_date' => 'date',
            'is_visible' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
