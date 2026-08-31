<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table
                ->foreignId('household_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('household_id');
        });
    }
};
