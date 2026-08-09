<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionCategoryRule extends Model
{
    protected $fillable = [
        'household_id',
        'category_id',
        'priority',
        'match_type',
        'match_text',
        'normalized_payee',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
