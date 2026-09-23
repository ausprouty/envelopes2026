<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Existing description becomes our user-entered details.
            $table->renameColumn('description', 'details');
        });

        Schema::table('transactions', function (Blueprint $table) {
            // Existing bank payee becomes the bank description.
            $table->renameColumn('payee', 'description');
        });

        
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Reverse in the opposite order.
            $table->renameColumn('description', 'payee');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('details', 'description');
        });
    }
};
