<?php

namespace App\Models;
use App\Enums\GeniusPayStatus;
use App\Enums\PaymentChannel;
use App\Enums\PaymentOperator;
use App\Enums\PaymentStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasUuids;
    protected $table = 'payments';

    protected $fillable = [
        'payer_id', 'payee_id', 'contract_id', 'subscription_id',
        'type', 'genius_pay_transaction_id', 'genius_pay_status',
        'payment_method', 'payment_channel', 'operator_id',
        'customer_phone', 'customer_email',
        'gross_amount_xof', 'commission_xof', 'net_amount_xof',
        'currency', 'status', 'provider_ref', 'provider_response',
        'initiated_at', 'confirmed_at', 'failed_at', 'refunded_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => PaymentStatus::class,
            'genius_pay_status' => GeniusPayStatus::class,
            'payment_channel' => PaymentChannel::class,
            'operator_id' => PaymentOperator::class,
            'provider_response' => 'array',
            'gross_amount_xof' => 'decimal:2',
            'commission_xof' => 'decimal:2',
            'net_amount_xof' => 'decimal:2',
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

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(FreelanceSubscription::class, 'subscription_id');
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class, 'payment_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'payment_id');
    }
}
