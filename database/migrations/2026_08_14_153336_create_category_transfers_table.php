<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('from_category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('to_category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);

            $table->date('transfer_date');

            $table->string('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_transfers');
    }
};
