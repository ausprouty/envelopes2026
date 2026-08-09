<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeAllocationLine extends Model
{
    protected $fillable = [
        'income_allocation_id',
        'category_id',
        'amount',
        'balance_before',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
    ];

    public function incomeAllocation(): BelongsTo
    {
        return $this->belongsTo(IncomeAllocation::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
