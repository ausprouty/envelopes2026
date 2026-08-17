<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAccount extends Model
{
    protected $fillable = [
        'account_name',
        'account_reference',
        'account_type',
        'available_balance',
        'available_for_spending',
        'balance_as_of',
        'closed_at',
        'credit_limit',
        'currency',
        'display_order',
        'include_in_net_worth',
        'institution_name',
        'is_active',
        'ledger_balance',
        'legacy_paidby_id',
        'warning_balance',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'available_balance' => 'decimal:2',
            'available_for_spending' => 'boolean',
            'balance_as_of' => 'datetime',
            'closed_at' => 'date',
            'credit_limit' => 'decimal:2',
            'display_order' => 'integer',
            'include_in_net_worth' => 'boolean',
            'is_active' => 'boolean',
            'ledger_balance' => 'decimal:2',
            'warning_balance' => 'decimal:2',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function importProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            TransactionImportProfile::class,
            'financial_account_import_profile'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
