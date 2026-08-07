<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_tags', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')
                ->constrained('households')
                ->restrictOnDelete();

            $table->string('tag_name', 100);
            $table->string('tag_group', 50)->nullable();

            $table->timestamps();

            $table->unique(
                ['household_id', 'tag_name'],
                'account_tags_household_name_unique'
            );

            $table->index(
                ['household_id', 'tag_group'],
                'account_tags_household_group_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_tags');
    }
};
