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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('description')
                ->nullable()
                ->comment(
                    'Description supplied by the bank or imported transaction file.'
                )
                ->change();

            $table->string('details')
                ->nullable()
                ->comment(
                    'User-entered details for remembering the purpose of the '
                        . 'transaction or providing reimbursement information.'
                )
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('description')
                ->nullable()
                ->comment(null)
                ->change();

            $table->string('details')
                ->nullable()
                ->comment(null)
                ->change();
        });
    }
};
