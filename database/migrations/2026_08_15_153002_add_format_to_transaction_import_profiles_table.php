<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->string('format')
                ->default('csv');

            $table->string('date_column')
                ->nullable()
                ->change();

            $table->string('description_column')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_import_profiles', function (Blueprint $table) {
            $table->dropColumn('format');

            $table->string('date_column')
                ->nullable(false)
                ->change();

            $table->string('description_column')
                ->nullable(false)
                ->change();
        });
    }
};
