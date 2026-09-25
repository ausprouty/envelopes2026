<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->string('bank_record_id_column')
                ->nullable()
                ->after('description_column')
                ->comment(
                    'For CSV imports: column containing the bank-supplied unique transaction identifier. '
                    . 'If NULL, duplicate prevention falls back to the latest transaction date.'
                );
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_external_id_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('external_id', 'bank_record_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(
                ['financial_account_id', 'bank_record_id'],
                'transactions_account_bank_record_id_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_account_bank_record_id_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('bank_record_id', 'external_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('external_id', 'transactions_external_id_index');
        });

        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->dropColumn('bank_record_id_column');
        });
    }
};
