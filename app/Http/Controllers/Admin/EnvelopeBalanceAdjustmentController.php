<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EnvelopeBalanceAdjustment;
use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EnvelopeBalanceAdjustmentController extends Controller
{
    public function create(
        Household $household
    ): Response {
        $categories = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('category_type', '!=', 'heading')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'current_balance' => (float) $category->current_balance,
                    'dashboard_image' => $category->dashboard_image,
                ];
            });

        return Inertia::render(
            'admin/households/balance-adjustments/Create',
            [
                'categories' => $categories,
                'household' => $household,
            ]
        );
    }

    public function store(
        Request $request,
        Household $household
    ): RedirectResponse {
        $validated = $request->validate([
            'adjustment_date' => [
                'required',
                'date',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            'balances' => [
                'required',
                'array',
            ],

            'balances.*.category_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'balances.*.balance' => [
                'required',
                'numeric',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $household
        ) {
            foreach ($validated['balances'] as $balance) {
                $category = Category::query()
                    ->where('household_id', $household->id)
                    ->where('is_active', true)
                    ->where('tracks_balance', true)
                    ->findOrFail($balance['category_id']);

                $desiredBalance = (float) $balance['balance'];

                $currentBalance = (float) $category->current_balance;

                $adjustmentAmount =
                    $desiredBalance - $currentBalance;

                if (abs($adjustmentAmount) < 0.005) {
                    continue;
                }

                EnvelopeBalanceAdjustment::create([
                    'household_id' => $household->id,
                    'category_id' => $category->id,
                    'adjustment_date' => $validated['adjustment_date'],
                    'amount' => $adjustmentAmount,
                    'reason' => $validated['reason'] ?? null,
                ]);

                $category->current_balance = $desiredBalance;
                $category->save();
            }
        });

        return redirect()
            ->route(
                'households.dashboard',
                $household
            )
            ->with(
                'success',
                'Opening balances updated successfully.'
            );
    }
}
