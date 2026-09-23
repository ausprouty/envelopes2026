<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->string('ledger_balance_column')->nullable();
            $table->string('available_balance_column')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'ledger_balance_column',
                'available_balance_column',
            ]);
        });
    }
};
