<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE financial_accounts
            MODIFY account_type ENUM(
                'cash',
                'checking',
                'credit_card',
                'crypto',
                'investment',
                'loan',
                'ministry',
                'other',
                'reimbursement',
                'retirement',
                'savings',
                'superannuation',
                'term_deposit',
                'virtual'
            ) NOT NULL DEFAULT 'other'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE financial_accounts
            MODIFY account_type ENUM(
                'cash',
                'checking',
                'credit_card',
                'crypto',
                'investment',
                'ministry',
                'other',
                'reimbursement',
                'retirement',
                'savings',
                'superannuation',
                'term_deposit',
                'virtual'
            ) NOT NULL DEFAULT 'other'
        ");
    }
};
