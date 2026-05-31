<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioFile extends Model
{
    use HasUuids;
    protected $table = 'portfolio_files';

    protected $fillable = [
        'item_id', 'file_url', 'file_type', 'file_size_kb',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PortfolioItem::class, 'item_id');
    }
}
