<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\TransactionCategoryRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinancialTransactionController extends Controller
{
    public function index(
        Request $request,
        Household $household
    ): Response {
        // We'll use the same household authorization pattern
        // as the accounts controller.


        $transactions = Transaction::query()
            ->where('household_id', $household->id)
            ->with([
                'financialAccount',
                'category',
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('households/transactions/Index', [
            'household' => $household,
            'transactions' => $transactions,
        ]);
    }
    public function assign(
        Household $household
    ): Response {
        while (true) {
            $transaction = Transaction::query()
                ->where('household_id', $household->id)
                ->whereNull('category_id')
                ->whereNull('deferred_at')
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->first();

            if (! $transaction) {
                break;
            }

            $rule = $this->matchingRule(
                $household,
                $transaction->payee ?? ''
            );

            if (! $rule) {
                break;
            }

            $category = Category::query()
                ->where('household_id', $household->id)
                ->where('id', $rule->category_id)
                ->first();

            if (! $category) {
                // The rule points to a category that no longer exists.
                // Stop automatic assignment and show the transaction manually.
                break;
            }
            $transaction->update([
                'category_id' => $category->id,
            ]);

            if ($category->tracks_balance) {
                $category->increment(
                    'current_balance',
                    (float) $transaction->amount
                );
            }
        }

        return Inertia::render('households/transactions/Assign', [
            'household' => $household,

            'transaction' => $transaction,

            'categories' => Category::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->where('category_type', '!=', 'heading')
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'remaining' => Transaction::query()
                ->where('household_id', $household->id)
                ->whereNull('category_id')
                ->count(),
        ]);
    }

    public function defer(
        Household $household,
        Transaction $transaction
    ): RedirectResponse {
        abort_unless(
            $transaction->household_id === $household->id,
            404
        );

        $transaction->update([
            'deferred_at' => now(),
        ]);

        return redirect()->route(
            'households.transactions.assign',
            $household
        );
    }

    public function updateCategory(
        Request $request,
        Household $household,
        Transaction $transaction
    ): RedirectResponse {
        abort_unless(
            $transaction->household_id === $household->id,
            404
        );

        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where(
                        fn($query) => $query->where(
                            'household_id',
                            $household->id
                        )
                    ),
            ],

            'always' => ['required', 'boolean'],

            'match_type' => [
                'nullable',
                Rule::in([
                    'exact',
                    'contains',
                    'starts_with',
                ]),
            ],

            'match_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'normalized_payee' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $oldCategoryId = $transaction->category_id;

        $newCategory = Category::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['category_id']);

        if ($oldCategoryId && $oldCategoryId !== $newCategory->id) {
            $oldCategory = Category::query()
                ->where('household_id', $household->id)
                ->find($oldCategoryId);

            if ($oldCategory && $oldCategory->tracks_balance) {
                $oldCategory->decrement(
                    'current_balance',
                    (float) $transaction->amount
                );
            }
        }

        if ($oldCategoryId !== $newCategory->id) {
            if ($newCategory->tracks_balance) {
                $newCategory->increment(
                    'current_balance',
                    (float) $transaction->amount
                );
            }

            $transaction->update([
                'category_id' => $newCategory->id,
            ]);
        }

        if ($validated['always']) {
            TransactionCategoryRule::updateOrCreate(
                [
                    'household_id' => $household->id,
                    'match_type' => $validated['match_type'] ?? 'contains',
                    'match_text' => trim(
                        $validated['match_text'] ?? $transaction->payee
                    ),
                ],
                [
                    'category_id' => $validated['category_id'],
                    'priority' => 100,
                    'normalized_payee' => filled(
                        $validated['normalized_payee'] ?? null
                    )
                        ? trim($validated['normalized_payee'])
                        : null,
                    'is_active' => true,
                ]
            );
        }

        return back();
    }

    private function matchingRule(
        Household $household,
        string $payee
    ): ?TransactionCategoryRule {
        $payee = trim($payee);

        return TransactionCategoryRule::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderBy('match_text')
            ->get()
            ->first(function (TransactionCategoryRule $rule) use ($payee) {
                $matchText = trim($rule->match_text);

                return match ($rule->match_type) {
                    'exact' => Str::lower($payee)
                        === Str::lower($matchText),

                    'starts_with' => Str::startsWith(
                        Str::lower($payee),
                        Str::lower($matchText)
                    ),

                    'contains' => Str::contains(
                        Str::lower($payee),
                        Str::lower($matchText)
                    ),

                    default => false,
                };
            });
    }
}
