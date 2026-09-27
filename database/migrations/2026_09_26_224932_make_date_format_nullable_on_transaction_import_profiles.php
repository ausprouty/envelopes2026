<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->string('date_format', 30)
                ->nullable()
                ->default(null)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->string('date_format', 30)
                ->default('m/d/Y')
                ->nullable(false)
                ->change();
        });
    }
};
