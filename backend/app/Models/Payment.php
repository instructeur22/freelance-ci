<?php

namespace App\Models;
use App\Enums\PaymentChannel;
use App\Enums\PaymentOperator;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasUuids;
    protected $table = 'payments';

    protected $fillable = [
        'contract_id', 'user_id', 'transaction_id',
        'amount', 'platform_fee', 'net_amount',
        'currency', 'status', 'channel', 'operator',
        'transaction_type', 'reference', 'description',
        'metadata', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'channel' => PaymentChannel::class,
            'operator' => PaymentOperator::class,
            'transaction_type' => TransactionType::class,
            'amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class, 'payment_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'payment_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
