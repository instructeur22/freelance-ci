<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Enums\GeniusPayStatus;
use App\Enums\PaymentChannel;
use App\Enums\PaymentOperator;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'transactions';

    protected $fillable = [
        'user_id', 'type', 'amount', 'currency', 'description',
        'payment_channel', 'payment_operator', 'operator_status',
        'operator_transaction_id', 'operator_reference', 'payment_url',
        'metadata', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'payment_channel' => PaymentChannel::class,
            'payment_operator' => PaymentOperator::class,
            'operator_status' => GeniusPayStatus::class,
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'transaction_id');
    }
}
