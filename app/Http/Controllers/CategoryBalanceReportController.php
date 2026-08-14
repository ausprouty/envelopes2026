<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryBalanceReportController extends Controller
{
    public function index(
        Request $request,
        Household $household
    ): Response {
        $context = $request->string('context')->toString();

        if (! in_array($context, ['household', 'ministry_au'], true)) {
            $context = 'household';
        }

        $hasAuMinistryCategories = Category::query()
            ->where('household_id', $household->id)
            ->where('context', 'ministry_au')
            ->where('is_active', true)
            ->exists();

        $headings = Category::query()
            ->where('household_id', $household->id)
            ->where('context', $context)
            ->where('category_type', 'heading')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $heading) use ($household, $context) {
                $categories = Category::query()
                    ->where('household_id', $household->id)
                    ->where('context', $context)
                    ->where('parent_category_id', $heading->id)
                    ->where('is_active', true)
                    ->where('tracks_balance', true)
                    ->orderBy('name')
                    ->get()
                    ->map(function (Category $category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'balance' => (float) $category->current_balance,
                        ];
                    });

                return [
                    'id' => $heading->id,
                    'name' => $heading->name,
                    'categories' => $categories,
                    'balance' => (float) $categories->sum('balance'),
                ];
            })
            ->filter(fn ($heading) => $heading['categories']->isNotEmpty())
            ->values();

        $totalBalance = $headings->sum('balance');

        return Inertia::render('households/reports/CategoryBalances', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],

            'headings' => $headings,

            'totalBalance' => (float) $totalBalance,

            'reportContext' => $context,

            'hasAuMinistryCategories' => $hasAuMinistryCategories,
        ]);
    }
}
