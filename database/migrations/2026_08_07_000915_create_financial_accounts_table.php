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
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')
                ->constrained('households')
                ->restrictOnDelete();

            $table->integer('legacy_paidby_id')->nullable();

            $table->string('account_name', 150);
            $table->string('institution_name', 100)->nullable();

            $table->enum('account_type', [
                'cash',
                'checking',
                'savings',
                'credit_card',
                'term_deposit',
                'investment',
                'retirement',
                'superannuation',
                'crypto',
                'reimbursement',
                'ministry',
                'virtual',
                'other',
            ])->default('other');

            $table->char('currency', 3);

            $table->string('account_reference', 100)->nullable();
            $table->string('website')->nullable();

            $table->decimal('warning_balance', 14, 2)->nullable();
            $table->decimal('credit_limit', 14, 2)->nullable();

            $table->boolean('include_in_net_worth')->default(true);
            $table->boolean('available_for_spending')->default(false);
            $table->boolean('is_active')->default(true);

            $table->date('closed_at')->nullable();
            $table->integer('display_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['household_id', 'legacy_paidby_id'],
                'financial_accounts_household_legacy_unique'
            );

            $table->index(
                ['household_id', 'is_active'],
                'financial_accounts_household_active_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
