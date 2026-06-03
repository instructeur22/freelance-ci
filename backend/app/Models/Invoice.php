<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'invoices';

    protected $fillable = [
        'contract_id', 'payment_id', 'issued_to_id', 'invoice_number',
        'total_xof', 'platform_fee', 'net_amount', 'tax_xof',
        'currency', 'status', 'issue_date', 'due_date', 'paid_date',
        'invoice_data', 'pdf_url',
    ];

    protected function casts(): array
    {
        return [
            'total_xof' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'tax_xof' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
            'paid_date' => 'date',
            'invoice_data' => 'array',
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
