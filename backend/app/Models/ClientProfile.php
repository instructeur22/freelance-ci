<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProfile extends Model
{
    use HasUuids;
    protected $table = 'client_profiles';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'company_name', 'company_sector', 'company_size',
        'siret', 'total_spent_xof', 'projects_count',
    ];

    protected function casts(): array
    {
        return [
            'total_spent_xof' => 'decimal:2',
            'projects_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
