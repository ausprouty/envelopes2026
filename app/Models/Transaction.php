<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Transaction extends Model
{
    protected $fillable = [
        'household_id',
        'financial_account_id',
        'transaction_date',
        'description',
        'payee',
        'amount',
        'currency',
        'posted_date',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'posted_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }
}
