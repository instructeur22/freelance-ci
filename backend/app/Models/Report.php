<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'reports';

    protected $fillable = [
        'reporter_id', 'reported_user_id', 'reported_project_id',
        'type', 'description', 'evidence', 'admin_notes', 'status',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ReportType::class,
            'status' => ReportStatus::class,
            'evidence' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reported(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
}
