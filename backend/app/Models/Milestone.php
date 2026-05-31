<?php

namespace App\Models;

use App\Enums\MilestoneStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    use HasUuids;
    protected $table = 'milestones';

    protected $fillable = [
        'contract_id', 'title', 'description', 'amount_xof',
        'due_date', 'delivered_at', 'validated_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount_xof' => 'decimal:2',
            'due_date' => 'date',
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
