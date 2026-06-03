<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\GeniusPayStatus;
use App\Enums\PaymentChannel;
use App\Enums\PaymentOperator;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'payments';

    protected $fillable = [
        'contract_id', 'payer_id', 'payee_id', 'legacy_user_id', 'transaction_id',
        'genius_pay_transaction_id', 'genius_pay_status',
        'payment_channel', 'operator_id',
        'customer_phone', 'customer_email',
        'amount', 'gross_amount_xof', 'platform_fee', 'commission_xof',
        'net_amount_xof', 'legacy_channel', 'legacy_operator',
        'currency', 'status', 'transaction_type', 'reference', 'description',
        'metadata', 'provider_response',
        'paid_at', 'initiated_at', 'confirmed_at', 'failed_at', 'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'genius_pay_status' => GeniusPayStatus::class,
            'payment_channel' => PaymentChannel::class,
            'operator_id' => PaymentOperator::class,
            'transaction_type' => TransactionType::class,
            'amount' => 'decimal:2',
            'gross_amount_xof' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'commission_xof' => 'decimal:2',
            'net_amount_xof' => 'decimal:2',
            'metadata' => 'array',
            'provider_response' => 'array',
            'paid_at' => 'datetime',
            'initiated_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'failed_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
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
