<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->decimal('available_balance', 15, 2)->nullable();
            $table->dateTime('balance_as_of')->nullable();
            $table->decimal('ledger_balance', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'available_balance',
                'balance_as_of',
                'ledger_balance',
            ]);
        });
    }
};
