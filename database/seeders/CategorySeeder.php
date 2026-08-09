<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $bobHouseholdId = DB::table('households')
            ->where('household_name', 'Bob and Chris')
            ->value('id');

        $ryanHouseholdId = DB::table('households')
            ->where('household_name', 'Ryan and Hailey')
            ->value('id');

        if (! $bobHouseholdId || ! $ryanHouseholdId) {
            throw new RuntimeException(
                'Households must be seeded before categories.'
            );
        }

        $categories = [
            [
                'household_id' => $bobHouseholdId,
                'code' => 'income_pool',
                'name' => 'Income Pool',
                'parent_category_id' => null,
                'category_type' => 'income',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 0,
            ],
            [
                'household_id' => $bobHouseholdId,
                'code' => 'P8-420',
                'name' => 'Groceries',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 10,
            ],
            [
                'household_id' => $bobHouseholdId,
                'code' => 'H6-7410',
                'name' => 'Ministry Travel',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => false,
                'is_active' => true,
                'display_order' => 20,
            ],
            [
                'household_id' => $bobHouseholdId,
                'code' => 'income_pool',
                'name' => 'Income Pool',
                'parent_category_id' => null,
                'category_type' => 'income',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 0,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Groceries',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 10,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Housing',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 20,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Vehicle',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 30,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Regular Bills',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 40,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Debt Payments',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 50,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Personal Spending',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 60,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Savings',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 70,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'code' => null,
                'name' => 'Other',
                'parent_category_id' => null,
                'category_type' => 'expense',
                'context' => 'household',
                'tracks_balance' => true,
                'is_active' => true,
                'display_order' => 80,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                [
                    'household_id' => $category['household_id'],
                    'name' => $category['name'],
                ],
                [
                    ...$category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
