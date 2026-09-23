<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_account_balance_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_account_id')
                ->constrained('financial_accounts')
                ->cascadeOnDelete();

            $table->decimal('balance', 14, 2);

            $table->date('balance_date');

            $table->string('source', 50)->nullable();

            $table->timestamps();

            $table->unique(
                ['financial_account_id', 'balance_date'],
                'financial_account_balance_date_unique'
            );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_account_balance_histories');
    }
};
