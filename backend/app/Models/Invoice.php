<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasUuids;
    protected $table = 'invoices';

    protected $fillable = [
        'payment_id', 'invoice_number', 'issued_to_id', 'issued_at',
        'pdf_url', 'total_xof', 'tax_xof', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_xof' => 'decimal:2',
            'tax_xof' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to_id');
    }
}
