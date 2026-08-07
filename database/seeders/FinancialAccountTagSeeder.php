<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialAccountTagSeeder extends Seeder
{
    public function run(): void
    {
        $bobHouseholdId = DB::table('households')
            ->where('household_name', 'Bob and Chris')
            ->value('id');

        if (! $bobHouseholdId) {
            throw new RuntimeException(
                'Bob and Chris household must exist before assigning account tags.'
            );
        }

        $accountId = DB::table('financial_accounts')
            ->where('household_id', $bobHouseholdId)
            ->where('account_name', 'Westpac Choice')
            ->value('id');

        $tagId = DB::table('account_tags')
            ->where('household_id', $bobHouseholdId)
            ->where('tag_name', 'Ready cash')
            ->value('id');

        if (! $accountId || ! $tagId) {
            throw new RuntimeException(
                'Westpac Choice and Ready cash must exist before creating their link.'
            );
        }

        DB::table('financial_account_tags')->updateOrInsert([
            'financial_account_id' => $accountId,
            'account_tag_id' => $tagId,
        ]);
    }
}
