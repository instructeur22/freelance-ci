<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioFile extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'portfolio_files';

    protected $fillable = [
        'portfolio_item_id', 'file_url', 'file_type', 'file_size',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PortfolioItem::class, 'portfolio_item_id');
    }
}
