<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escrow extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'escrows';

    protected $fillable = [
        'contract_id', 'payment_id', 'dispute_id', 'status', 'amount',
        'held_amount', 'released_amount', 'refunded_amount',
        'held_at', 'released_at', 'release_requested_at', 'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EscrowStatus::class,
            'amount' => 'decimal:2',
            'held_amount' => 'decimal:2',
            'released_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'held_at' => 'datetime',
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
}
