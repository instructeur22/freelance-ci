<?php

namespace App\Models;
use App\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    use HasUuids;
    protected $table = 'quotes';

    protected $fillable = [
        'project_id', 'freelance_id', 'amount_xof', 'duration_days',
        'cover_letter', 'status', 'accepted_at', 'refused_at', 'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'amount_xof' => 'decimal:2',
            'accepted_at' => 'datetime',
            'refused_at' => 'datetime',
            'withdrawn_at' => 'datetime',
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
