<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'messages';

    protected $fillable = [
        'conversation_id', 'sender_id', 'content',
        'status', 'read_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MessageStatus::class,
            'read_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MessageFile::class, 'message_id');
    }
}
