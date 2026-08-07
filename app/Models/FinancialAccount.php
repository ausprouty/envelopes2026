<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAccount extends Model
{
    protected $fillable = [
        'legacy_paidby_id',
        'account_name',
        'institution_name',
        'account_type',
        'currency',
        'account_reference',
        'website',
        'warning_balance',
        'credit_limit',
        'include_in_net_worth',
        'available_for_spending',
        'is_active',
        'closed_at',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'warning_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'include_in_net_worth' => 'boolean',
            'available_for_spending' => 'boolean',
            'is_active' => 'boolean',
            'closed_at' => 'date',
            'display_order' => 'integer',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
