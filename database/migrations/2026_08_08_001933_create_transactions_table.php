<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('financial_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('transaction_date');

            $table->string('description')->nullable();

            $table->string('payee')->nullable();

            $table->decimal('amount', 15, 2);

            $table->char('currency', 3);

            $table->date('posted_date')->nullable();

            $table->text('comment')->nullable();

            $table->timestamps();

            $table->index([
                'household_id',
                'transaction_date',
            ]);

            $table->index([
                'financial_account_id',
                'transaction_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
