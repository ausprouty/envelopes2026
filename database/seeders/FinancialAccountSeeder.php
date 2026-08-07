<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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
                'Households must be seeded before financial accounts.'
            );
        }

        $accounts = [
            [
                'household_id' => $bobHouseholdId,
                'legacy_paidby_id' => 30,
                'account_name' => 'Westpac Choice',
                'institution_name' => 'Westpac',
                'account_type' => 'checking',
                'currency' => 'AUD',
                'account_reference' => 'Ending 2358',
                'website' => null,
                'warning_balance' => 5000.00,
                'credit_limit' => null,
                'include_in_net_worth' => true,
                'available_for_spending' => true,
                'is_active' => true,
                'closed_at' => null,
                'display_order' => 10,
            ],
            [
                'household_id' => $bobHouseholdId,
                'legacy_paidby_id' => 31,
                'account_name' => 'Westpac MasterCard',
                'institution_name' => 'Westpac',
                'account_type' => 'credit_card',
                'currency' => 'AUD',
                'account_reference' => 'Ending 1234',
                'website' => null,
                'warning_balance' => null,
                'credit_limit' => 5000.00,
                'include_in_net_worth' => true,
                'available_for_spending' => false,
                'is_active' => true,
                'closed_at' => null,
                'display_order' => 20,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'legacy_paidby_id' => null,
                'account_name' => 'Main Checking',
                'institution_name' => null,
                'account_type' => 'checking',
                'currency' => 'USD',
                'account_reference' => null,
                'website' => null,
                'warning_balance' => 500.00,
                'credit_limit' => null,
                'include_in_net_worth' => true,
                'available_for_spending' => true,
                'is_active' => true,
                'closed_at' => null,
                'display_order' => 10,
            ],
            [
                'household_id' => $ryanHouseholdId,
                'legacy_paidby_id' => null,
                'account_name' => 'Credit Card',
                'institution_name' => null,
                'account_type' => 'credit_card',
                'currency' => 'USD',
                'account_reference' => null,
                'website' => null,
                'warning_balance' => null,
                'credit_limit' => 3000.00,
                'include_in_net_worth' => true,
                'available_for_spending' => false,
                'is_active' => true,
                'closed_at' => null,
                'display_order' => 20,
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('financial_accounts')->updateOrInsert(
                [
                    'household_id' => $account['household_id'],
                    'account_name' => $account['account_name'],
                ],
                [
                    ...$account,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
