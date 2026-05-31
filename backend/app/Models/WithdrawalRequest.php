<?php

namespace App\Models;
use App\Enums\WithdrawalMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    use HasUuids;
    protected $table = 'withdrawal_requests';

    protected $fillable = [
        'user_id', 'amount_xof', 'method', 'phone', 'operator',
        'status', 'processed_at', 'processed_by', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'decimal:2',
            'method' => WithdrawalMethod::class,
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
