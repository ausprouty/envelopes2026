<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'amount',
        'bank_record_id',
        'category_id',
        'comment',
        'currency',
        'expense_type',
        'deferred_at',
        'description',
        'details',
        'financial_account_id',
        'household_id',
        'import_hash',
        'import_source',
        'posted_date',
        'transaction_date',
        'transfer_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'posted_date' => 'date',
            'transaction_date' => 'date',
        ];
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function transferTransaction(): BelongsTo
    {
        return $this->belongsTo(
            Transaction::class,
            'transfer_transaction_id'
        );
    }

    public function transferredFrom()
    {
        return $this->hasOne(
            Transaction::class,
            'transfer_transaction_id'
        );
    }

    public function splits(): HasMany
    {
        return $this->hasMany(TransactionSplit::class);
    }
}
