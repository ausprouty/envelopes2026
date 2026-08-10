<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    protected $fillable = [
        'household_id',
        'code',
        'name',
        'parent_category_id',
        'category_type',
        'context',
        'tracks_balance',
        'is_active',
        'display_order',
        'icon',
        'needs_attention',
        'dashboard_image',
    ];

    protected function casts(): array
    {
        return [
            'tracks_balance' => 'boolean',
            'needs_attention' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            Category::class,
            'parent_category_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            Category::class,
            'parent_category_id'
        );
    }
    public function incomeAllocationLines(): HasMany
    {
        return $this->hasMany(IncomeAllocationLine::class);
    }
    public function incomeAllocationDefault(): HasOne
    {
        return $this->hasOne(IncomeAllocationDefault::class);
    }
}
