<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAccount extends Model
{
    protected $fillable = [
        'account_name',
        'account_reference',
        'account_type',
        'available_for_spending',
        'closed_at',
        'credit_limit',
        'currency',
        'display_order',
        'include_in_net_worth',
        'institution_name',
        'is_active',
        'legacy_paidby_id',
        'transaction_import_profile_id',
        'warning_balance',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'available_for_spending' => 'boolean',
            'closed_at' => 'date',
            'credit_limit' => 'decimal:2',
            'display_order' => 'integer',
            'include_in_net_worth' => 'boolean',
            'is_active' => 'boolean',
            'warning_balance' => 'decimal:2',
        ];
    }


    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function importProfile()
    {
        return $this->belongsTo(
            TransactionImportProfile::class,
            'transaction_import_profile_id'
        );
    }
    public function transactionImportProfile(): BelongsTo
    {
        return $this->belongsTo(TransactionImportProfile::class);
    }
}
