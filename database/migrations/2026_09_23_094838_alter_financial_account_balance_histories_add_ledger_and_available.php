<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_account_balance_histories', function (Blueprint $table) {
            $table->decimal('ledger_balance', 14, 2)
                ->nullable()
                ->after('financial_account_id');

            $table->decimal('available_balance', 14, 2)
                ->nullable()
                ->after('ledger_balance');

            $table->dropColumn('balance');
        });
    }

    public function down(): void
    {
        Schema::table('financial_account_balance_histories', function (Blueprint $table) {
            $table->decimal('balance', 14, 2)
                ->nullable()
                ->after('financial_account_id');

            $table->dropColumn([
                'ledger_balance',
                'available_balance',
            ]);
        });
    }
};
