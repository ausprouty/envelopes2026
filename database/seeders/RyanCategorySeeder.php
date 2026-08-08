<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class RyanCategorySeeder extends Seeder
{
    public function run(): void
    {
        $householdId = 2;

        /*
         * Money
         */
        $money = $this->heading(
            $householdId,
            '010',
            'Money',
            10
        );

        $this->category(
            $householdId,
            '011',
            'Bank and Overdraft Fees',
            'expense',
            $money->id,
            true,
            11
        );

        $this->category(
            $householdId,
            '012',
            'Deposits',
            'income',
            $money->id,
            false,
            12
        );

        $this->category(
            $householdId,
            '013',
            'Income',
            'income',
            $money->id,
            false,
            13
        );

        $this->category(
            $householdId,
            '014',
            'Transfers Between People or Accounts',
            'transfer',
            $money->id,
            false,
            14
        );

        /*
         * Debt Payments
         */
        $debt = $this->heading(
            $householdId,
            '050',
            'Debt Payments',
            50
        );

        $this->category(
            $householdId,
            '051',
            'Credit Card Paydown',
            'expense',
            $debt->id,
            true,
            51
        );

        /*
         * Family
         */
        $family = $this->heading(
            $householdId,
            '100',
            'Family',
            100
        );

        $this->category(
            $householdId,
            '110',
            'Dog',
            'expense',
            $family->id,
            true,
            110
        );

        $this->category(
            $householdId,
            '120',
            'Entertainment',
            'expense',
            $family->id,
            true,
            120
        );

        $this->category(
            $householdId,
            '130',
            'Outings and Eating Out',
            'expense',
            $family->id,
            true,
            130
        );

        $this->category(
            $householdId,
            '140',
            'Vacation and Travel',
            'expense',
            $family->id,
            true,
            140
        );

        /*
         * Household
         */
        $household = $this->heading(
            $householdId,
            '200',
            'Household',
            200
        );

        $this->category(
            $householdId,
            '210',
            'Daily Expenses',
            'expense',
            $household->id,
            true,
            210
        );

        $this->category(
            $householdId,
            '220',
            'Groceries',
            'expense',
            $household->id,
            true,
            220
        );

        $this->category(
            $householdId,
            '230',
            'Home Repairs',
            'expense',
            $household->id,
            true,
            230
        );

        $this->category(
            $householdId,
            '240',
            'Household Supplies',
            'expense',
            $household->id,
            true,
            240
        );

        $this->category(
            $householdId,
            '250',
            'Utilities',
            'expense',
            $household->id,
            true,
            250
        );

        /*
         * Personal
         */
        $personal = $this->heading(
            $householdId,
            '300',
            'Personal',
            300
        );

        $this->category(
            $householdId,
            '310',
            'Medical and Health',
            'expense',
            $personal->id,
            true,
            310
        );

        $this->category(
            $householdId,
            '320',
            'Personal Spending',
            'expense',
            $personal->id,
            true,
            320
        );

        /*
         * Transportation
         */
        $transportation = $this->heading(
            $householdId,
            '400',
            'Transportation',
            400
        );

        $this->category(
            $householdId,
            '410',
            'Car Insurance',
            'expense',
            $transportation->id,
            true,
            410
        );

        $this->category(
            $householdId,
            '420',
            'Car Repayment',
            'expense',
            $transportation->id,
            true,
            420
        );

        $this->category(
            $householdId,
            '430',
            'Car Repairs & Maintenance',
            'expense',
            $transportation->id,
            true,
            430
        );

        $this->category(
            $householdId,
            '440',
            'Gas',
            'expense',
            $transportation->id,
            true,
            440
        );

        $this->category(
            $householdId,
            '450',
            'Parking & Tolls',
            'expense',
            $transportation->id,
            true,
            450
        );

        $this->category(
            $householdId,
            '460',
            'Registration & Fees',
            'expense',
            $transportation->id,
            true,
            460
        );

        /*
         * Standalone categories
         */
        $this->category(
            $householdId,
            '500',
            'Subscriptions and Online Services',
            'expense',
            null,
            true,
            500
        );

        $this->category(
            $householdId,
            '999',
            'Unknown - Needs Review',
            'expense',
            null,
            false,
            999
        );
    }

    private function heading(
        int $householdId,
        string $code,
        string $name,
        int $displayOrder
    ): Category {
        return Category::updateOrCreate(
            [
                'household_id' => $householdId,
                'name' => $name,
            ],
            [
                'code' => $code,
                'parent_category_id' => null,
                'category_type' => 'heading',
                'context' => 'household',
                'tracks_balance' => false,
                'is_active' => true,
                'display_order' => $displayOrder,
            ]
        );
    }

    private function category(
        int $householdId,
        string $code,
        string $name,
        string $type,
        ?int $parentId,
        bool $tracksBalance,
        int $displayOrder
    ): void {
        Category::updateOrCreate(
            [
                'household_id' => $householdId,
                'name' => $name,
            ],
            [
                'code' => $code,
                'parent_category_id' => $parentId,
                'category_type' => $type,
                'context' => 'household',
                'tracks_balance' => $tracksBalance,
                'is_active' => true,
                'display_order' => $displayOrder,
            ]
        );
    }
}
