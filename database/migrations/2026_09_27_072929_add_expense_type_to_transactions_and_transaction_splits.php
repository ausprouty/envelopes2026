<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('expense_type')
                ->nullable()
                ->after('category_id');
        });

        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->string('expense_type')
                ->nullable()
                ->after('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_splits', function (Blueprint $table) {
            $table->dropColumn('expense_type');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('expense_type');
        });
    }
};
