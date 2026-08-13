<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transactions', 'external_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('external_id')
                    ->nullable()
                    ->after('comment');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'external_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('external_id');
            });
        }
    }
};
