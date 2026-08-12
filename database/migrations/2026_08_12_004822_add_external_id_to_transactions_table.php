<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('external_id')
                ->nullable()
                ->after('comment');

            $table->unique(
                ['financial_account_id', 'external_id'],
                'transactions_account_external_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(
                'transactions_account_external_id_unique'
            );

            $table->dropColumn('external_id');
        });
    }
};
