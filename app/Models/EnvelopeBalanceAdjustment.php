<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvelopeBalanceAdjustment extends Model
{
    protected $fillable = [
        'household_id',
        'category_id',
        'adjustment_date',
        'amount',
        'reason',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
