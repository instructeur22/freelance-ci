<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'data',
        'action_url', 'is_read', 'read_at',
        'sent_email', 'sent_push',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'sent_email' => 'boolean',
            'sent_push' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
