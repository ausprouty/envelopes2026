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
    public function review(
        Household $household
    ): Response {
        while (true) {
            $transaction = Transaction::query()
                ->where('household_id', $household->id)
                ->whereNull('category_id')
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

            $transaction->update([
                'category_id' => $rule->category_id,
            ]);
        }

        return Inertia::render('households/transactions/Review', [
            'household' => $household,

            'transaction' => $transaction,

            'categories' => Category::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
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

        $transaction->update([
            'category_id' => $validated['category_id'],
        ]);

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
