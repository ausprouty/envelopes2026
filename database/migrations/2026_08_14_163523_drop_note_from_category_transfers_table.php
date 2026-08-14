<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_transfers', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }

    public function down(): void
    {
        Schema::table('category_transfers', function (Blueprint $table) {
            $table->string('note')->nullable();
        });
    }
};
