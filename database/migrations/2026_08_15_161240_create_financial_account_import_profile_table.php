<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_account_import_profile', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_account_id');

            $table->foreign(
                'financial_account_id',
                'faip_account_fk'
            )
                ->references('id')
                ->on('financial_accounts')
                ->cascadeOnDelete();

            $table->foreignId('transaction_import_profile_id');

            $table->foreign(
                'transaction_import_profile_id',
                'faip_profile_fk'
            )
                ->references('id')
                ->on('transaction_import_profiles')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'financial_account_id',
                'transaction_import_profile_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_account_import_profile');
    }
};
