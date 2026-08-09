<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeAllocation extends Model
{
    protected $fillable = [
        'household_id',
        'allocation_date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(IncomeAllocationLine::class);
    }
}
