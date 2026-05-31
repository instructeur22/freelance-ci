<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasUuids;
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id', 'payment_id', 'amount_xof', 'direction',
        'balance_after_xof', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'decimal:2',
            'balance_after_xof' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'user_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
