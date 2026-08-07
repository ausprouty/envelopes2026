<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountTagSeeder extends Seeder
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
                'Households must be seeded before account tags.'
            );
        }

        $tags = [
            [
                'household_id' => $bobHouseholdId,
                'tag_name' => 'Ministry funds',
                'tag_group' => 'purpose',
            ],
            [
                'household_id' => $bobHouseholdId,
                'tag_name' => 'Ready cash',
                'tag_group' => 'liquidity',
            ],
            [
                'household_id' => $bobHouseholdId,
                'tag_name' => 'Centrelink reportable',
                'tag_group' => 'reporting',
            ],
            [
                'household_id' => $ryanHouseholdId,
                'tag_name' => 'Daily spending',
                'tag_group' => 'purpose',
            ],
        ];

        foreach ($tags as $tag) {
            DB::table('account_tags')->updateOrInsert(
                [
                    'household_id' => $tag['household_id'],
                    'tag_name' => $tag['tag_name'],
                ],
                [
                    'tag_group' => $tag['tag_group'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
