<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_pool_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamp('processed_at');

            $table->timestamps();

            $table->index([
                'household_id',
                'processed_at',
            ]);
        });

        Schema::create('income_pool_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('income_pool_batch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('transaction_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->timestamps();

            /*
             * An income transaction can only enter
             * the Income Pool once.
             */
            $table->unique('transaction_id');

            $table->index('income_pool_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_pool_entries');
        Schema::dropIfExists('income_pool_batches');
    }
};
