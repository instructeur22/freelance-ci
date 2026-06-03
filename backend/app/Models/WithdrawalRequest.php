<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\WithdrawalMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'withdrawal_requests';

    protected $fillable = [
        'wallet_id', 'user_id', 'amount', 'fee', 'net_amount',
        'genius_pay_transfer_id', 'bank_account', 'phone_number',
        'withdrawal_method', 'account_identifier', 'status',
        'admin_notes', 'processed_by', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'bank_account' => 'array',
            'withdrawal_method' => WithdrawalMethod::class,
            'processed_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
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
