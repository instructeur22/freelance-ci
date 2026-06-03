<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = 'contracts';

    protected $fillable = [
        'project_id', 'quote_id', 'client_id', 'freelance_id',
        'title', 'description', 'total_amount', 'currency',
        'platform_fee', 'commission_rate', 'commission_xof',
        'freelance_amount',
        'start_date', 'end_date', 'terms_conditions', 'terms_text', 'status',
        'client_signed_at', 'freelance_signed_at',
        'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'total_amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_xof' => 'decimal:2',
            'freelance_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'client_signed_at' => 'datetime',
            'freelance_signed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function freelance(): BelongsTo
    {
        return $this->belongsTo(User::class, 'freelance_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class, 'contract_id');
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class, 'contract_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'contract_id');
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class, 'contract_id');
    }
}
