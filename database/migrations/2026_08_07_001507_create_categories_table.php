<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')
                ->constrained('households')
                ->restrictOnDelete();

            $table->string('code', 20)->nullable();
            $table->string('name', 150);

            $table->foreignId('parent_category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->enum('category_type', [
                'income',
                'expense',
                'asset',
                'transfer',
                'reimbursement',
                'heading',
            ]);

            $table->enum('context', [
                'household',
                'ministry_au',
                'ministry_us',
                'other',
            ])->default('household');

            $table->boolean('tracks_balance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['household_id', 'name'],
                'categories_household_name_unique'
            );

            $table->unique(
                ['household_id', 'code'],
                'categories_household_code_unique'
            );

            $table->index(
                ['household_id', 'is_active', 'display_order'],
                'categories_household_active_order_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
