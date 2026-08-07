<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HouseholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('households')->insert([
            [
                'household_name' => 'Bob and Chris',
                'default_currency' => 'AUD',
                'timezone' => 'Australia/Sydney',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'household_name' => 'Ryan and Hailey',
                'default_currency' => 'USD',
                'timezone' => 'America/Los_Angeles',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
