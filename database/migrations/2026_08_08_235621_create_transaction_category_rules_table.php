<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_category_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            // Lower numbers are checked first.
            $table->unsignedInteger('priority')
                ->default(100);

            // exact, contains, starts_with
            $table->string('match_type', 20)
                ->default('contains');

            // What we look for in the bank's raw payee text.
            $table->string('match_text');

            // Optional cleaned-up payee name.
            $table->string('normalized_payee')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index([
                'household_id',
                'is_active',
                'priority',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_category_rules');
    }
};
