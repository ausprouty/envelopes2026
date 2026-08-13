<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TransactionCategoryRule;
use Illuminate\Database\Seeder;
use RuntimeException;

class TransactionCategoryRuleSeeder extends Seeder
{
    public function run(): void
    {
        $householdId = 1;

        $rules = [
            [
                'match_text' => 'CHEMIST WAREHOUSE',
                'legacy_account_id' => 'P8-430',
                'category_name' => 'Daily',
            ],
            [
                'match_text' => 'OVERDRAWN FEE',
                'legacy_account_id' => 'P8-430',
                'category_name' => 'Daily',
            ],
            [
                'match_text' => 'OVERSEAS TRANSACTION',
                'legacy_account_id' => 'P8-430',
                'category_name' => 'Daily',
            ],
            [
                'match_text' => 'REWARD PROGRAM FEE',
                'legacy_account_id' => 'P8-430',
                'category_name' => 'Daily',
            ],
            [
                'match_text' => 'DUTCHBROS',
                'legacy_account_id' => 'P8-420',
                'category_name' => 'Groceries',
            ],
            [
                'match_text' => 'EVERYPLATE',
                'legacy_account_id' => 'P8-420',
                'category_name' => 'Groceries',
            ],
            [
                'match_text' => 'SAFEWAY',
                'legacy_account_id' => 'P8-420',
                'category_name' => 'Groceries',
            ],
            [
                'match_text' => 'ORIGIN ELEC',
                'legacy_account_id' => 'P8-510',
                'category_name' => 'Utilities',
            ],
            [
                'match_text' => 'ORIGIN GAS',
                'legacy_account_id' => 'P8-510',
                'category_name' => 'Utilities',
            ],
            [
                'match_text' => 'OSTEOPATH',
                'legacy_account_id' => 'P11-100',
                'category_name' => 'Medical Expense',
            ],
            [
                'match_text' => 'PHYSIO',
                'legacy_account_id' => 'P11-100',
                'category_name' => 'Medical Expense',
            ],
            [
                'match_text' => 'CHIROPRACTIC',
                'legacy_account_id' => 'P11-100',
                'category_name' => 'Medical Expense',
            ],
            [
                'match_text' => 'LIDCOMBE SERVICE STA',
                'legacy_account_id' => 'P8-820',
                'category_name' => 'Petrol',
            ],
            [
                'match_text' => 'LINKT SYDNEY',
                'legacy_account_id' => 'P8-820',
                'category_name' => 'Petrol',
            ],
            [
                'match_text' => 'TOWNE PUMP',
                'legacy_account_id' => 'P8-820',
                'category_name' => 'Petrol',
            ],
            [
                'match_text' => 'PETROL',
                'legacy_account_id' => 'P8-820',
                'category_name' => 'Petrol',
            ],
            [
                'match_text' => 'KIVA.ORG',
                'legacy_account_id' => 'P8-710',
                'category_name' => 'Tithe',
            ],
            [
                'match_text' => 'TRANSPORT FOR NSW',
                'legacy_account_id' => 'P8-810',
                'category_name' => 'Train',
            ],
            [
                'match_text' => 'TRANSPORTFORNSW',
                'legacy_account_id' => 'P8-810',
                'category_name' => 'Train',
            ],
            [
                'match_text' => 'CCCU VISA CARD PAYMENT',
                'legacy_account_id' => 'P4-900',
                'category_name' => 'Transfers of Cash',
            ],
            [
                'match_text' => 'MONTHLY TRANSFER',
                'legacy_account_id' => 'P4-900',
                'category_name' => 'Transfers of Cash',
            ],
            [
                'match_text' => 'TRANSFER FROM WEST',
                'legacy_account_id' => 'P4-900',
                'category_name' => 'Transfers of Cash',
            ],
        ];

        foreach ($rules as $rule) {
            $category = Category::query()
                ->where('household_id', $householdId)
                ->where('name', $rule['category_name'])
                ->first();

            if (! $category) {
                throw new RuntimeException(
                    "Category not found: {$rule['category_name']} "
                    . "(legacy account {$rule['legacy_account_id']})"
                );
            }

            TransactionCategoryRule::updateOrCreate(
                [
                    'household_id' => $householdId,
                    'match_type' => 'contains',
                    'match_text' => $rule['match_text'],
                ],
                [
                    'category_id' => $category->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
