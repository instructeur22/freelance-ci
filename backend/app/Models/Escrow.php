<?php

namespace App\Models;
use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escrow extends Model
{
    use HasUuids;
    protected $table = 'escrows';

    protected $fillable = [
        'contract_id', 'payment_id', 'amount_xof', 'status',
        'held_at', 'release_requested_at', 'released_at', 'refunded_at', 'dispute_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => EscrowStatus::class,
            'amount_xof' => 'decimal:2',
            'held_at' => 'datetime',
            'release_requested_at' => 'datetime',
            'released_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'dispute_id');
    }
}
