<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_account_tags', function (Blueprint $table) {
            $table->foreignId('financial_account_id')
                ->constrained('financial_accounts')
                ->cascadeOnDelete();

            $table->foreignId('account_tag_id')
                ->constrained('account_tags')
                ->cascadeOnDelete();

            $table->primary([
                'financial_account_id',
                'account_tag_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_account_tags');
    }
};
