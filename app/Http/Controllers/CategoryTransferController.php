<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryTransfer;
use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CategoryTransferController extends Controller
{


    public function create(
        Household $household
    ): Response {
        $categories = Category::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('category_type', '!=', 'heading')
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'current_balance' => (float) $category->current_balance,
                    'context' => $category->context,
                ];
            });

        return Inertia::render('households/category-transfers/Create', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],
            'categories' => $categories,
        ]);
    }
    public function store(
        Request $request,
        Household $household
    ): RedirectResponse {
        $validated = $request->validate([
            'from_category_id' => ['required', 'integer'],
            'to_category_id' => ['required', 'integer', 'different:from_category_id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transfer_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $household) {
            $fromCategory = Category::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->where('tracks_balance', true)
                ->where('category_type', '!=', 'heading')
                ->findOrFail($validated['from_category_id']);

            $toCategory = Category::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->where('tracks_balance', true)
                ->where('category_type', '!=', 'heading')
                ->findOrFail($validated['to_category_id']);

            if ($fromCategory->context !== $toCategory->context) {
                throw ValidationException::withMessages([
                    'to_category_id' => 'Choose an envelope from the same area.',
                ]);
            }

            if ((float) $fromCategory->current_balance < (float) $validated['amount']) {
                throw ValidationException::withMessages([
                    'amount' => 'There is not enough money in this envelope.',
                ]);
            }

            $fromCategory->decrement(
                'current_balance',
                $validated['amount']
            );

            $toCategory->increment(
                'current_balance',
                $validated['amount']
            );

            CategoryTransfer::create([
                'household_id' => $household->id,
                'from_category_id' => $fromCategory->id,
                'to_category_id' => $toCategory->id,
                'amount' => $validated['amount'],
                'transfer_date' => $validated['transfer_date'],
                'description' => $validated['description'] ?? null,

            ]);
        });

        return redirect()
            ->route('households.category-transfers.create', [
                'household' => $household->id,
            ])
            ->with('success', 'Money moved successfully.');
    }
}
