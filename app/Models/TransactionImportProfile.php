<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionImportProfile extends Model
{
    protected $fillable = [
        'name',
        'format',
        'header_signature',
        'date_column',
        'description_column',
        'amount_column',
        'debit_column',
        'credit_column',
        'date_format',
        'payee_field',
        'description_field',
    ];

    public function financialAccounts(): HasMany
    {
        return $this->hasMany(FinancialAccount::class);
    }
}
