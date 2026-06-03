<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id', 'payment_id', 'type', 'direction', 'amount_xof',
        'balance_before_xof', 'balance_after_xof',
        'description', 'reference', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'decimal:2',
            'balance_before_xof' => 'decimal:2',
            'balance_after_xof' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
