<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Models\IncomeAllocationDefault;
use App\Models\Transaction;
use App\Models\TransactionSplit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpendingReportController extends Controller
{
    public function index(
        Request $request,
        Household $household
    ): Response {

        $context = $request->string('context')->toString();

        if (! in_array($context, ['household', 'ministry_au'])) {
            $context = 'household';
        }

        $view = $request->string('view')->toString();

        if (! in_array($view, ['month', 'year'])) {
            $view = 'month';
        }

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $categories = Category::query()
            ->where('household_id', $household->id)
            ->where('context', $context)
            ->where('is_active', true)
            ->whereIn('category_type', ['heading', 'expense'])
            ->orderBy('display_order')
            ->get();

        $defaults = IncomeAllocationDefault::query()
            ->where('household_id', $household->id)
            ->whereIn('category_id', $categories->pluck('id'))
            ->pluck('amount', 'category_id');

        $categoryIds = $categories->pluck('id');

        $transactionSpending = Transaction::query()
            ->selectRaw('
                category_id,
                MONTH(transaction_date) as month,
                SUM(amount * -1) as total
            ')
            ->where('household_id', $household->id)
            ->whereYear('transaction_date', $year)
            ->whereNotNull('category_id')
            ->whereIn('category_id', $categoryIds)
            ->groupBy('category_id')
            ->groupByRaw('MONTH(transaction_date)')
            ->get();

        $splitSpending = TransactionSplit::query()
            ->selectRaw('
                transaction_splits.category_id,
                MONTH(transactions.transaction_date) as month,
                SUM(transaction_splits.amount * -1) as total
            ')
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_splits.transaction_id'
            )
            ->where('transactions.household_id', $household->id)
            ->whereYear('transactions.transaction_date', $year)
            ->whereIn('transaction_splits.category_id', $categoryIds)
            ->groupBy('transaction_splits.category_id')
            ->groupByRaw('MONTH(transactions.transaction_date)')
            ->get();

        $monthlySpending = [];

        foreach ($transactionSpending as $spending) {
            $monthlySpending[$spending->category_id][$spending->month]
                = (float) $spending->total;
        }

        foreach ($splitSpending as $spending) {
            $categoryId = $spending->category_id;
            $spendingMonth = $spending->month;

            $monthlySpending[$categoryId][$spendingMonth]
                = ($monthlySpending[$categoryId][$spendingMonth] ?? 0)
                + (float) $spending->total;
        }

        $rows = $categories->map(function (Category $category) use (
            $defaults,
            $monthlySpending,
            $month
        ) {
            $usualAllocation = (float) ($defaults[$category->id] ?? 0);

            $months = [];

            for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++) {
                $actual = (float) (
                    $monthlySpending[$category->id][$monthNumber] ?? 0
                );

                $difference = $actual - $usualAllocation;

                $percent = $usualAllocation > 0
                    ? ($difference / $usualAllocation) * 100
                    : null;

                $status = 'normal';

                if ($percent !== null) {
                    if ($percent > 20) {
                        $status = 'over';
                    } elseif ($percent < -20) {
                        $status = 'under';
                    }
                }

                $months[] = [
                    'month' => $monthNumber,
                    'actual' => $actual,
                    'difference' => $difference,
                    'percent' => $percent,
                    'status' => $status,
                ];
            }

            $yearTotal = collect($months)->sum('actual');

            $monthsWithData = collect($months)
                ->filter(fn($item) => $item['actual'] != 0)
                ->count();

            $average = $monthsWithData > 0
                ? $yearTotal / $monthsWithData
                : 0;

            $selectedMonth = collect($months)
                ->firstWhere('month', $month);

            return [
                'id' => $category->id,
                'parent_category_id' => $category->parent_category_id,
                'name' => $category->name,
                'code' => $category->code,
                'category_type' => $category->category_type,

                'dashboard_image' => $category->dashboard_image,

                'usual_allocation' => $usualAllocation,

                'selected_month' => $selectedMonth,

                'months' => $months,

                'year_total' => $yearTotal,
                'average' => $average,
            ];
        });

        return Inertia::render('households/reports/SpendingByCategory', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],

            'context' => $context,
            'view' => $view,
            'year' => $year,
            'month' => $month,

            'rows' => $rows,
        ]);
    }
}
