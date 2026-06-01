<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GeniusPayWebhook extends Model
{
    use HasUuids;
    protected $table = 'genius_pay_webhooks';

    protected $fillable = [
        'event_type', 'payload', 'signature',
        'is_processed', 'processed_at', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }
}
