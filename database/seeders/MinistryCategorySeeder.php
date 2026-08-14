<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class MinistryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $householdId = 1;

        $groups = [
            [
                'heading' => [
                    'code' => 'H6-7100',
                    'name' => 'Ministry Expansion',
                ],
                'children' => [
                    ['code' => 'H6-7110', 'name' => 'Recruiting'],
                    ['code' => 'H6-7120', 'name' => 'Entertainment / Hospitality'],
                    ['code' => 'H6-7156', 'name' => 'Project Expenses'],
                ],
            ],

            [
                'heading' => [
                    'code' => 'H6-7105',
                    'name' => 'Training',
                ],
                'children' => [
                    ['code' => 'H6-7115', 'name' => 'Training and Development','gst_default' => false],
                    ['code' => 'H6-7116', 'name' => 'PTC Conferences', 'gst_default' => false],
                    ['code' => 'H6-7155', 'name' => "Leader's Retreat / Orientation", 'gst_default' => false],
                ],
            ],

            [
                'heading' => [
                    'code' => 'H6-7200',
                    'name' => 'Office Expenses',
                ],
                'children' => [
                    ['code' => 'H6-7210', 'name' => 'Office Supplies'],
                    ['code' => 'H6-7225', 'name' => 'Office Personal Expenses'],
                    ['code' => 'H6-7240', 'name' => 'Furniture Purchases'],
                    ['code' => 'H6-7241', 'name' => 'Equipment Purchases'],
                    ['code' => 'H6-7250', 'name' => 'Computer Purchases'],
                    ['code' => 'H6-7260', 'name' => 'Computer Supplies'],
                    ['code' => 'H6-7270', 'name' => 'Software Purchases'],
                    ['code' => 'H6-7280', 'name' => 'Subscriptions'],
                    ['code' => 'H6-7310', 'name' => 'General Material Purchases'],
                    ['code' => 'H6-7319', 'name' => 'General Material Purchases - CCCI'],
                    ['code' => 'H6-7320', 'name' => 'Websites and Brochures'],
                    ['code' => 'H6-7330', 'name' => 'Broadcasting'],
                    ['code' => 'H6-7340', 'name' => 'Program Editing'],
                    ['code' => 'H6-7380', 'name' => 'Advertisement Expense'],
                    ['code' => 'H6-7385', 'name' => 'Printing'],
                ],
            ],

            [
                'heading' => [
                    'code' => 'H6-7400',
                    'name' => 'Travel and Transportation',
                ],
                'children' => [
                    ['code' => 'H6-7410', 'name' => 'Travel & Transportation'],
                    ['code' => 'H6-7420', 'name' => 'Accommodation / Meals'],
                    ['code' => 'H6-7450', 'name' => 'Ministry Vehicle Expense'],
                    ['code' => 'H6-7490', 'name' => 'Other Travel Expenses'],
                ],
            ],

            [
                'heading' => [
                    'code' => 'H6-7500',
                    'name' => 'Communication',
                ],
                'children' => [
                    ['code' => 'H6-7510', 'name' => 'Phone / Fax Charges'],
                    ['code' => 'H6-7512', 'name' => 'Mobile Call Charges'],
                    ['code' => 'H6-7515', 'name' => 'Internet Charges'],
                    ['code' => 'H6-7520', 'name' => 'Postage and Freight','gst_default' => false],
                ],
            ],

            [
                'heading' => [
                    'code' => 'H6-7700',
                    'name' => 'Repairs and Maintenance',
                ],
                'children' => [
                    ['code' => 'H6-7720', 'name' => 'Repairs and Maintenance - Building'],
                    ['code' => 'H6-7750', 'name' => 'Repairs and Maintenance - Equipment'],
                    ['code' => 'H6-7760', 'name' => 'Repairs and Maintenance - Computers'],
                ],
            ],

            [
                'heading' => [
                    'code' => 'H6-7800',
                    'name' => 'Facility',
                ],
                'children' => [
                    ['code' => 'H6-7810', 'name' => 'Rent','gst_default' => false],
                    ['code' => 'H6-7820', 'name' => 'Utilities'],
                    ['code' => 'H6-7830', 'name' => 'General Insurance'],
                    ['code' => 'H6-7850', 'name' => 'Other Facility Expenses'],
                    ['code' => 'H6-7885', 'name' => 'Cleaning'],
                ],
            ],

            [
                'heading' => [
                    'code' => 'H6-7900',
                    'name' => 'Miscellaneous',
                ],
                'children' => [

                    ['code' => 'H6-7930', 'name' => 'Professional Fees','gst_default' => false],
                    ['code' => 'H6-7940', 'name' => 'Bank Fees', 'gst_default' => false],
                    ['code' => 'H6-7950', 'name' => 'General Donations - Third Parties', 'gst_default' => false],
                    ['code' => 'H6-7960', 'name' => 'Non-Financial Gifts (Outreach)' ,'gst_default' => false],
                    ['code' => 'H6-7970', 'name' => 'Miscellaneous Expense','gst_default' => false],
                    ['code' => 'H6-7990', 'name' => 'Other Miscellaneous Expenses','gst_default' => false],
                ],
            ]
        ];

        $displayOrder = 10;

        foreach ($groups as $group) {
            $heading = Category::updateOrCreate(
                [
                    'household_id' => $householdId,
                    'code' => $group['heading']['code'],
                ],
                [
                    'name' => $group['heading']['name'],
                    'parent_category_id' => null,
                    'category_type' => 'heading',
                    'context' => 'ministry_au',
                    'tracks_balance' => false,
                    'is_active' => true,
                    'gst_default' => false,
                    'display_order' => $displayOrder,
                ]
            );

            $displayOrder += 10;

            foreach ($group['children'] as $child) {
                Category::updateOrCreate(
                    [
                        'household_id' => $householdId,
                        'code' => $child['code'],
                    ],
                    [
                        'name' => $child['name'],
                        'parent_category_id' => $heading->id,
                        'category_type' => 'expense',
                        'context' => 'ministry_au',
                        'tracks_balance' => true,
                        'gst_default' => $child['gst_default'] ?? true,
                        'is_active' => true,
                        'display_order' => $displayOrder,
                    ]
                );

                $displayOrder += 10;
            }
        }
    }
}
