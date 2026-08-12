<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class BobCategorySeeder extends Seeder
{
    public function run(): void
    {
        $householdId = 1;

        /*
        |--------------------------------------------------------------------------
        | Headings
        |--------------------------------------------------------------------------
        |
        | Desired order:
        | Income
        | Chris
        | Bob
        | Together
        | then alphabetical
        |
        */

        $headings = [
            [
                'code' => 'P4-100',
                'name' => 'INCOME',
                'display_order' => 1000,
                'dashboard_image' => 'money.png',
            ],
            [
                'code' => 'P8-200',
                'name' => 'CHRIS',
                'display_order' => 2000 ,
                'dashboard_image' => 'personal.png',
            ],
            [
                'code' => 'P8-300',
                'name' => 'BOB',
                'display_order' => 3000,
                'dashboard_image' => 'personal.png',
            ],
            [
                'code' => 'P8-100',
                'name' => 'TOGETHER',
                'display_order' => 4000,
                'dashboard_image' => 'family.png',
            ],
            [
                'code' => 'P8-500',
                'name' => 'HOUSE',
                'display_order' => 5000,
                'dashboard_image' => 'home.png',
            ],
            [
                'code' => 'P8-400',
                'name' => 'HOUSEHOLD',
                'display_order' => 6000,
                'dashboard_image' => 'household_supplies.png',
            ],
            [
                'code' => 'P11-100',
                'name' => 'MEDICAL',
                'display_order' => 7000,
                'dashboard_image' => 'medical_and_health.png',
            ],
            [
                'code' => 'P8-600',
                'name' => 'SET ASIDE',
                'display_order' => 8000,
                'dashboard_image' => 'money.png',
            ],
            [
                'code' => 'P8-700',
                'name' => 'TITHE',
                'display_order' => 9000,
                'dashboard_image' => 'tithe.png',
            ],
            [
                'code' => 'P8-800',
                'name' => 'TRANSPORT',
                'display_order' => 10000,
                'dashboard_image' => 'transport.png',
            ],
        ];

        $headingIds = [];

        foreach ($headings as $heading) {
            $category = Category::updateOrCreate(
                [
                    'household_id' => $householdId,
                    'name' => $heading['name'],
                ],
                [
                    'code' => $heading['code'],
                    'parent_category_id' => null,
                    'category_type' => 'heading',
                    'context' => 'household',
                    'tracks_balance' => false,
                    'is_active' => true,
                    'display_order' => $heading['display_order'],
                    'needs_attention' => false,
                    'dashboard_image' => $heading['dashboard_image'] ?? null,
                ]
            );

            $headingIds[$heading['name']] = $category->id;
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = [

            /*
            |--------------------------------------------------------------------------
            | INCOME
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'INCOME',
                'code' => 'P3-100',
                'name' => 'Annuity Income - Bob ($624)',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-200',
                'name' => 'Annuity Income - Chris ($675)',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-101-B',
                'name' => 'Bitcoin Income - Bob',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-101-C',
                'name' => 'Bitcoin Income - Chris',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P4-101',
                'name' => 'Cru (net pay) - Bob ($360)',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P4-102',
                'name' => 'Cru (net pay) - Chris ($360)',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-102-B',
                'name' => 'IRA redemption - Bob',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-102-C',
                'name' => 'IRA redemption - Chris',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-105-B',
                'name' => 'Roth IRA redemption - Bob',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-105-C',
                'name' => 'Roth IRA redemption - Chris',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P4-104',
                'name' => 'Scripture Union Fringe',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P4-103',
                'name' => 'Scripture Union Pay Received',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-104',
                'name' => 'Stock Dividends - Australia',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P2-500',
                'name' => 'Stock Sales - Australia',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-106-B',
                'name' => 'Super redemption - Bob',
                'type' => 'income',
            ],
            [
                'heading' => 'INCOME',
                'code' => 'P3-103',
                'name' => 'Super redemption - Chris',
                'type' => 'income',
            ],

            /*
            |--------------------------------------------------------------------------
            | CHRIS
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'CHRIS',
                'code' => 'P8-210',
                'name' => "Chris's Account",
                'dashboard_image' => 'personal.png',
            ],

            /*
            |--------------------------------------------------------------------------
            | BOB
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'BOB',
                'code' => 'P8-310',
                'name' => "Bob's Account",
                'dashboard_image' => 'personal.png',
            ],

            /*
            |--------------------------------------------------------------------------
            | TOGETHER
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'TOGETHER',
                'code' => 'P8-151',
                'name' => 'Bottle Money',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-110',
                'name' => 'Clothing',
                'dashboard_image' => 'personal_expenses.png',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-120',
                'name' => 'Dates',
                'dashboard_image' => 'outings_and_eating_out.png',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-130',
                'name' => 'Entertainment',
                'dashboard_image' => 'entertainment.png',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-140',
                'name' => 'Exercise',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-150',
                'name' => 'Family Outings',
                'dashboard_image' => 'outings_and_eating_out.png',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-160',
                'name' => 'Gifts',
                'dashboard_image' => 'gifts.png',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-170',
                'name' => 'Possessions',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-155',
                'name' => 'Vacation',
                'dashboard_image' => 'vacation_and_travel.png',
            ],
            [
                'heading' => 'TOGETHER',
                'code' => 'P8-152',
                'name' => 'Visiting Family',
                'dashboard_image' => 'family.png',
            ],

            /*
            |--------------------------------------------------------------------------
            | HOUSE
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'HOUSE',
                'code' => 'P8-510',
                'name' => 'Housing Fund',
                'dashboard_image' => 'home.png',
            ],

            /*
            |--------------------------------------------------------------------------
            | HOUSEHOLD
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'HOUSEHOLD',
                'code' => 'P8-430',
                'name' => 'Daily',
                'dashboard_image' => 'daily_expenses.png',
            ],
            [
                'heading' => 'HOUSEHOLD',
                'code' => 'P8-420',
                'name' => 'Groceries',
                'dashboard_image' => 'groceries.png',
            ],

            /*
            |--------------------------------------------------------------------------
            | MEDICAL
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'MEDICAL',
                'code' => 'P11-200',
                'name' => 'Dental Expense',
                'dashboard_image' => 'medical_and_health.png',
            ],
            [
                'heading' => 'MEDICAL',
                'code' => 'P11-170',
                'name' => 'Medical Expense',
                'dashboard_image' => 'medical_and_health.png',
            ],
            [
                'heading' => 'MEDICAL',
                'code' => 'P11-160',
                'name' => 'Medical - from Medicare',
                'dashboard_image' => 'medical_and_health.png',
            ],
            [
                'heading' => 'MEDICAL',
                'code' => 'P11-101',
                'name' => 'Medical - Workers Comp',
                'dashboard_image' => 'medical_and_health.png',
            ],
            [
                'heading' => 'MEDICAL',
                'code' => 'P11-150',
                'name' => 'Medical from CRU',
                'dashboard_image' => 'medical_and_health.png',
            ],

            /*
            |--------------------------------------------------------------------------
            | SET ASIDE
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'SET ASIDE',
                'code' => 'P8-630',
                'name' => 'Emergency',
                'dashboard_image' => 'money.png',
            ],
            [
                'heading' => 'SET ASIDE',
                'code' => 'P8-611',
                'name' => 'Fraud',
            ],
            [
                'heading' => 'SET ASIDE',
                'code' => 'P8-640',
                'name' => 'Insurance',
                'dashboard_image' => 'car_insurance.png',
            ],
            [
                'heading' => 'SET ASIDE',
                'code' => 'P8-620',
                'name' => 'Investing (AUS)',
                'dashboard_image' => 'money.png',
            ],
            [
                'heading' => 'SET ASIDE',
                'code' => 'P8-610',
                'name' => 'Lost Money',
            ],
            [
                'heading' => 'SET ASIDE',
                'code' => 'P8-670',
                'name' => 'Taxes (Australia)',
            ],
            [
                'heading' => 'SET ASIDE',
                'code' => 'P8-671',
                'name' => 'Taxes (USA)',
            ],

            /*
            |--------------------------------------------------------------------------
            | TITHE
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'TITHE',
                'code' => 'P8-710',
                'name' => 'Tithe Envelope',
                'dashboard_image' => 'tithe.png',
            ],

            /*
            |--------------------------------------------------------------------------
            | TRANSPORT
            |--------------------------------------------------------------------------
            */

            [
                'heading' => 'TRANSPORT',
                'code' => 'P8-840',
                'name' => 'Car Purchase',
                'dashboard_image' => 'transport.png',
            ],
            [
                'heading' => 'TRANSPORT',
                'code' => 'P8-830',
                'name' => 'Car Repair',
                'dashboard_image' => 'car_repairs_and_maintenance.png',
            ],
            [
                'heading' => 'TRANSPORT',
                'code' => 'P8-820',
                'name' => 'Petrol',
                'dashboard_image' => 'gas.png',
            ],
            [
                'heading' => 'TRANSPORT',
                'code' => 'P8-850',
                'name' => 'Rego and Insurance',
                'dashboard_image' => 'car_insurance.png',
            ],
            [
                'heading' => 'TRANSPORT',
                'code' => 'P8-810',
                'name' => 'Train',
                'dashboard_image' => 'transport.png',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Alphabetize categories inside each heading
        |--------------------------------------------------------------------------
        */

        $grouped = collect($categories)
            ->groupBy('heading');

        foreach ($headings as $heading) {
            $items = $grouped
                ->get($heading['name'], collect())
                ->sortBy(
                    fn (array $item) => strtolower($item['name'])
                )
                ->values();

            foreach ($items as $index => $item) {
                $categoryType = $item['type'] ?? 'expense';

                Category::updateOrCreate(
                    [
                        'household_id' => $householdId,
                        'name' => $item['name'],
                    ],
                    [
                        'code' => $item['code'],
                        'parent_category_id' => $headingIds[$item['heading']],
                        'category_type' => $categoryType,
                        'context' => 'household',

                        // Income categories classify incoming money.
                        // Expense categories are actual envelopes.
                        'tracks_balance' => $categoryType !== 'income',

                        'is_active' => true,

                        // Keeps children alphabetized underneath
                        // their heading.
                        'display_order' =>
                            $heading['display_order'] + (($index + 1) * 10),

                        'needs_attention' => false,
                        'dashboard_image' => $item['dashboard_image'] ?? null,
                    ]
                );
            }
        }
    }
}
