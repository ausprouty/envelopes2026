<?php

namespace Database\Seeders;

use App\Models\TransactionImportProfile;
use Illuminate\Database\Seeder;

class TransactionImportProfileSeeder extends Seeder
{
    public function run(): void
    {
        TransactionImportProfile::updateOrCreate(
            [
                'name' => 'Chase CSV',
            ],
            [
                'header_signature' => 'Details|Posting Date|Description|Amount|Type|Balance|Check or Slip #',
                'date_column' => 'Posting Date',
                'description_column' => 'Description',
                'amount_column' => 'Amount',
                'debit_column' => null,
                'credit_column' => null,
                'date_format' => 'm/d/Y',
            ]
        );
    }
}
