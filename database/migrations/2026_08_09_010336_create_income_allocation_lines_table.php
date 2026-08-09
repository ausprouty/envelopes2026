<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_allocation_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('income_allocation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);

            $table->decimal('balance_before', 12, 2);

            $table->timestamps();

            $table->unique([
                'income_allocation_id',
                'category_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_allocation_lines');
    }
};
