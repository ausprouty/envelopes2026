<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            /*
             * payee_field was originally used to separately select an
             * OFX/QBO payee field such as NAME.
             *
             * Transactions now keep one bank-supplied description.
             * The import profile's description_field determines which
             * OFX/QBO/QFX tag supplies that description.
             */
            $table->dropColumn('payee_field');

            $table->string('description_column')
                ->nullable()
                ->comment(
                    'For CSV imports: column containing the bank-supplied '
                    . 'transaction description. Not used for OFX/QBO/QFX.'
                )
                ->change();

            $table->string('description_field')
                ->nullable()
                ->comment(
                    'For OFX/QBO/QFX imports: tag such as MEMO or NAME '
                    . 'containing the bank-supplied transaction description. '
                    . 'Not used for CSV.'
                )
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->string('payee_field')
                ->nullable()
                ->after('updated_at')
                ->comment(
                    'Legacy OFX/QBO field formerly used to select a payee.'
                );

            $table->string('description_column')
                ->nullable()
                ->comment(null)
                ->change();

            $table->string('description_field')
                ->nullable()
                ->comment(null)
                ->change();
        });
    }
};
