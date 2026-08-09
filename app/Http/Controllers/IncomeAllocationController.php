<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Models\IncomeAllocation;
use App\Models\IncomeAllocationDefault;
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
            ->where('code', '!=', 'income_pool')
            ->where(function ($query) {
                $query
                    ->where('category_type', 'heading')
                    ->orWhere('tracks_balance', true);
            })
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) use ($household) {
                $isHeading = $category->category_type === 'heading';

                $default = $isHeading
                    ? null
                    : IncomeAllocationDefault::query()
                    ->where('household_id', $household->id)
                    ->where('category_id', $category->id)
                    ->first();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'is_heading' => $isHeading,

                    'current_balance' => $isHeading
                        ? null
                        : $this->currentBalance(
                            $household,
                            $category
                        ),

                    'normal_amount' => $default
                        ? (float) $default->amount
                        : 0,
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

        /*
     * How much is actually being allocated
     * in this allocation.
     */
        $allocatedTotal = collect($validated['lines'])
            ->sum(
                fn($line) => (float) $line['amount']
            );

        if ($allocatedTotal <= 0) {
            throw ValidationException::withMessages([
                'lines' => 'Enter at least one amount to allocate.',
            ]);
        }

        /*
     * How much is currently available
     * in the Income Pool.
     */
        $availableToAllocate = $this->availableToAllocate(
            $household
        );

        /*
     * Partial allocations are allowed.
     * We only prevent allocating MORE
     * than the Income Pool contains.
     */
        if ($allocatedTotal > $availableToAllocate + 0.005) {
            throw ValidationException::withMessages([
                'lines' => 'You cannot allocate more than is available in the Income Pool.',
            ]);
        }

        $categoryIds = collect($validated['lines'])
            ->pluck('category_id');

        $validCategoryCount = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->whereIn('id', $categoryIds)
            ->count();

        if ($validCategoryCount !== $categoryIds->unique()->count()) {
            throw ValidationException::withMessages([
                'lines' => 'One or more categories are invalid.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $household,
            $allocatedTotal
        ) {
            $allocation = IncomeAllocation::create([
                'household_id' => $household->id,
                'allocation_date' => $validated['allocation_date'],
                'amount' => $allocatedTotal,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['lines'] as $line) {
                if ((float) $line['amount'] <= 0) {
                    continue;
                }

                $category = Category::query()
                    ->where('household_id', $household->id)
                    ->where('is_active', true)
                    ->where('tracks_balance', true)
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
                'households.dashboard',
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

    public function saveDefaults(
        Request $request,
        Household $household
    ): RedirectResponse {
        $validated = $request->validate([
            'lines' => [
                'required',
                'array',
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

        foreach ($validated['lines'] as $line) {
            $category = Category::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->where('tracks_balance', true)
                ->findOrFail($line['category_id']);

            IncomeAllocationDefault::updateOrCreate(
                [
                    'household_id' => $household->id,
                    'category_id' => $category->id,
                ],
                [
                    'amount' => $line['amount'],
                ]
            );
        }

        return back()->with(
            'success',
            'Normal allocation saved.'
        );
    }
}
