<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'financial_account_balance_histories',
            function (Blueprint $table) {
                $table->decimal('ledger_balance', 14, 2)
                    ->nullable()
                    ->comment(
                        'Ledger balance reported by the bank for the '
                        . 'transaction/date represented by balance_date.'
                    )
                    ->change();

                $table->decimal('available_balance', 14, 2)
                    ->nullable()
                    ->comment(
                        'Available balance reported by the bank, when supplied.'
                    )
                    ->change();

                $table->date('balance_date')
                    ->comment(
                        'Date associated with this balance snapshot. '
                        . 'For imported transaction history, Envelopes keeps '
                        . 'the earliest available balance for each month.'
                    )
                    ->change();

                $table->string('source', 50)
                    ->nullable()
                    ->comment(
                        'How the balance was obtained, for example '
                        . 'csv_import, ofx_import, or manual.'
                    )
                    ->change();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'financial_account_balance_histories',
            function (Blueprint $table) {
                $table->decimal('ledger_balance', 14, 2)
                    ->nullable()
                    ->comment(null)
                    ->change();

                $table->decimal('available_balance', 14, 2)
                    ->nullable()
                    ->comment(null)
                    ->change();

                $table->date('balance_date')
                    ->comment(null)
                    ->change();

                $table->string('source', 50)
                    ->nullable()
                    ->comment(null)
                    ->change();
            }
        );
    }
};
