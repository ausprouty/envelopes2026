<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryTransfer extends Model
{
    protected $fillable = [
        'household_id',
        'from_category_id',
        'to_category_id',
        'amount',
        'transfer_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function fromCategory(): BelongsTo
    {
        return $this->belongsTo(
            Category::class,
            'from_category_id'
        );
    }

    public function toCategory(): BelongsTo
    {
        return $this->belongsTo(
            Category::class,
            'to_category_id'
        );
    }
}
