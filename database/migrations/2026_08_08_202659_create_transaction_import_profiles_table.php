<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_import_profiles', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Used to recognize the CSV format automatically.
            $table->text('header_signature')->nullable();

            $table->string('date_column');
            $table->string('description_column');

            // Some banks have one signed Amount column.
            $table->string('amount_column')->nullable();

            // Other banks have separate Debit and Credit columns.
            $table->string('debit_column')->nullable();
            $table->string('credit_column')->nullable();

            $table->string('date_format', 30)->default('m/d/Y');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_import_profiles');
    }
};
