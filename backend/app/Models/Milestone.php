<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'milestones';

    protected $fillable = [
        'contract_id', 'title', 'description', 'amount',
        'due_date', 'is_completed', 'completed_at',
        'sort_order', 'delivered_at', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function getStatusAttribute(): MilestoneStatus
    {
        if ($this->validated_at) return MilestoneStatus::Validated;
        if ($this->delivered_at) return MilestoneStatus::Delivered;
        return MilestoneStatus::Pending;
    }
}
