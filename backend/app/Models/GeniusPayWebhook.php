<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeniusPayWebhook extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'genius_pay_webhooks';

    protected $fillable = [
        'event_type', 'transaction_id', 'raw_payload', 'signature',
        'is_processed', 'processed_at', 'processed_by', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }
}
