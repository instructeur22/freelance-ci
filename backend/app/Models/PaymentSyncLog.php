<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentSyncLog extends Model
{
    use HasUuids;
    protected $table = 'payment_sync_log';

    protected $fillable = [
        'payment_id', 'sync_type', 'status', 'request_payload',
        'response_payload', 'error_message', 'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
