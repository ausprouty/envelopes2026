<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->foreignId('transaction_import_profile_id')
                ->nullable()
                ->after('household_id')
                ->constrained('transaction_import_profiles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'transaction_import_profile_id'
            );
        });
    }
};
