<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProfile extends Model
{
    use HasUuids;
    protected $table = 'client_profiles';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'company_name', 'company_website', 'company_size',
        'industry', 'total_projects_posted', 'total_spent', 'average_rating',
    ];

    protected function casts(): array
    {
        return [
            'total_spent' => 'decimal:2',
            'total_projects_posted' => 'integer',
            'average_rating' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
