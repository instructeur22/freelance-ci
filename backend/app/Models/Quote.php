<?php

namespace App\Models;
use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'quotes';

    protected $fillable = [
        'project_id', 'freelance_id', 'amount', 'currency',
        'estimated_duration', 'proposal', 'status',
        'read_at', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'amount' => 'decimal:2',
            'read_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function freelance(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelance_id');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class, 'quote_id');
    }
}
