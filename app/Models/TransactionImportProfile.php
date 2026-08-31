<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransactionImportProfile extends Model
{
    protected $fillable = [
        'amount_column',
        'credit_column',
        'date_column',
        'date_format',
        'debit_column',
        'description_column',
        'description_field',
        'format',
        'header_signature',
        'name',
        'payee_field',
    ];

    public function financialAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            FinancialAccount::class,
            'financial_account_import_profile'
        );
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
