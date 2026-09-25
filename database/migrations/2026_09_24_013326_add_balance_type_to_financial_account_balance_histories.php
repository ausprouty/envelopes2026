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
                $table->string('balance_type', 50)
                    ->nullable()
                    ->after('balance_date')
                    ->comment(
                        'Purpose of this saved balance snapshot. '
                        . 'Examples: monthly_opening, annual_maximum, year_end.'
                    );

                $table->string('source', 50)
                    ->nullable()
                    ->comment(
                        'How Envelopes obtained the balance. '
                        . 'Examples: csv_import, ofx_import, statement, manual.'
                    )
                    ->change();
            }
        );

        /*
         * Add the replacement index FIRST.
         *
         * financial_account_id is a foreign key. MySQL currently uses the
         * existing unique index beginning with financial_account_id to
         * support that foreign key. The new index must exist before the
         * old one can safely be removed.
         */
        Schema::table(
            'financial_account_balance_histories',
            function (Blueprint $table) {
                $table->unique(
                    [
                        'financial_account_id',
                        'balance_date',
                        'balance_type',
                    ],
                    'financial_account_balance_date_type_unique'
                );
            }
        );

        /*
         * Now that another index beginning with financial_account_id exists,
         * the old two-column unique index can be removed.
         */
        Schema::table(
            'financial_account_balance_histories',
            function (Blueprint $table) {
                $table->dropUnique(
                    'financial_account_balance_date_unique'
                );
            }
        );
    }

    public function down(): void
    {
        /*
         * Restore the old index FIRST so the foreign key always has
         * an index beginning with financial_account_id.
         */
        Schema::table(
            'financial_account_balance_histories',
            function (Blueprint $table) {
                $table->unique(
                    [
                        'financial_account_id',
                        'balance_date',
                    ],
                    'financial_account_balance_date_unique'
                );
            }
        );

        Schema::table(
            'financial_account_balance_histories',
            function (Blueprint $table) {
                $table->dropUnique(
                    'financial_account_balance_date_type_unique'
                );
            }
        );

        Schema::table(
            'financial_account_balance_histories',
            function (Blueprint $table) {
                $table->dropColumn('balance_type');

                $table->string('source', 50)
                    ->nullable()
                    ->comment(null)
                    ->change();
            }
        );
    }
};
