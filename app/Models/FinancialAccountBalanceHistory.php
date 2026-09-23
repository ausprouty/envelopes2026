<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAccountBalanceHistory extends Model
{
    protected $fillable = [
        'financial_account_id',
        'ledger_balance',
        'available_balance',
        'balance_date',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'ledger_balance' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'balance_date' => 'date',
        ];
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }
}
