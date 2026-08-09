<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Models\IncomeAllocation;
use App\Models\IncomeAllocationLine;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class IncomeAllocationController extends Controller
{
    public function create(
        Request $request,
        Household $household
    ): Response {
        $categories = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('code', '!=', 'income_pool')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) use ($household) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'current_balance' => $this->currentBalance(
                        $household,
                        $category
                    ),
                ];
            });

        return Inertia::render(
            'households/income-allocations/Create',
            [
                'household' => $household,
                'categories' => $categories,
                'availableToAllocate' => $this->availableToAllocate(
                    $household
                ),
            ]
        );
    }

    public function store(
        Request $request,
        Household $household
    ): RedirectResponse {

        $validated = $request->validate([
            'allocation_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'lines' => [
                'required',
                'array',
                'min:1',
            ],

            'lines.*.category_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'lines.*.amount' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);
        $availableToAllocate = $this->availableToAllocate(
            $household
        );

        if (
            abs(
                (float) $validated['amount']
                    - $availableToAllocate
            ) > 0.005
        ) {
            throw ValidationException::withMessages([
                'amount' => 'The amount available has changed. Please reload the allocation page.',
            ]);
        }

        $categoryIds = collect($validated['lines'])
            ->pluck('category_id');

        $validCategoryCount = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->whereIn('id', $categoryIds)
            ->count();

        if ($validCategoryCount !== $categoryIds->unique()->count()) {
            throw ValidationException::withMessages([
                'lines' => 'One or more categories are invalid.',
            ]);
        }

        $allocatedTotal = collect($validated['lines'])
            ->sum(fn($line) => (float) $line['amount']);

        if (abs($allocatedTotal - (float) $validated['amount']) > 0.005) {
            throw ValidationException::withMessages([
                'lines' => 'The allocation must equal the amount available.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $household
        ) {
            $allocation = IncomeAllocation::create([
                'household_id' => $household->id,
                'allocation_date' => $validated['allocation_date'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['lines'] as $line) {
                if ((float) $line['amount'] <= 0) {
                    continue;
                }

                $category = Category::query()
                    ->where('household_id', $household->id)
                    ->findOrFail($line['category_id']);

                $allocation->lines()->create([
                    'category_id' => $category->id,
                    'amount' => $line['amount'],
                    'balance_before' => $this->currentBalance(
                        $household,
                        $category
                    ),
                ]);
            }
        });

        return redirect()
            ->route(
                'households.transactions.index',
                $household
            )
            ->with(
                'success',
                'Income allocated successfully.'
            );
    }

    private function currentBalance(
        Household $household,
        Category $category
    ): float {
        $transactions = Transaction::query()
            ->where('household_id', $household->id)
            ->where('category_id', $category->id)
            ->sum('amount');

        $allocations = IncomeAllocationLine::query()
            ->where('category_id', $category->id)
            ->whereHas(
                'incomeAllocation',
                fn($query) => $query->where(
                    'household_id',
                    $household->id
                )
            )
            ->sum('amount');

        return (float) $transactions + (float) $allocations;
    }


    private function availableToAllocate(
        Household $household
    ): float {
        $incomePool = Category::query()
            ->where('household_id', $household->id)
            ->where('code', 'income_pool')
            ->firstOrFail();

        $incomeReceived = Transaction::query()
            ->where('household_id', $household->id)
            ->where('category_id', $incomePool->id)
            ->sum('amount');

        $alreadyAllocated = IncomeAllocation::query()
            ->where('household_id', $household->id)
            ->sum('amount');

        return (float) $incomeReceived
            - (float) $alreadyAllocated;
    }
}
