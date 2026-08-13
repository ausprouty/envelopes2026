<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->foreignId('financial_account_id')
                ->nullable()
                ->after('category_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('financial_account_id');

            $table->foreignId('category_id')
                ->nullable(false)
                ->change();
        });
    }
};
