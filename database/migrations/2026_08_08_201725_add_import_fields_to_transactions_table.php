<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('external_id')
                ->nullable()
                ->after('posted_date');

            $table->string('import_source', 50)
                ->nullable()
                ->after('external_id');

            $table->string('import_hash', 64)
                ->nullable()
                ->after('import_source');

            $table->index('external_id');
            $table->index('import_hash');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['external_id']);
            $table->dropIndex(['import_hash']);

            $table->dropColumn([
                'external_id',
                'import_source',
                'import_hash',
            ]);
        });
    }
};
