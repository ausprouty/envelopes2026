<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\TransactionCategoryRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FinancialTransactionController extends Controller
{

    public function assign(
        Request $request,
        Household $household
    ): Response {
        $showDeferred = $request->boolean('deferred');

        $hasAuMinistryCategories = Category::query()
            ->where('household_id', $household->id)
            ->where('context', 'ministry_au')
            ->where('is_active', true)
            ->exists();

        while (true) {
            $transaction = Transaction::query()
                ->where('household_id', $household->id)
                ->whereNull('category_id')
                ->whereDoesntHave('splits')
                ->when(
                    $showDeferred,
                    fn($query) => $query->whereNotNull('deferred_at'),
                    fn($query) => $query->whereNull('deferred_at')
                )
                ->when(
                    $showDeferred,
                    fn($query) => $query
                        ->orderBy('deferred_at')
                        ->orderBy('id'),
                    fn($query) => $query
                        ->orderBy('transaction_date')
                        ->orderBy('id')
                )
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
                break;
            }

            $gstAmount = null;

            if (
                $category->context === 'ministry_au' &&
                $category->gst_default
            ) {
                $gstAmount = round(
                    abs((float) $transaction->amount) / 11,
                    2
                );
            }

            $transaction->update([
                'category_id' => $category->id,
                'gst_amount' => $gstAmount,
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
            'showDeferred' => $showDeferred,
            'transaction' => $transaction,

            'hasAuMinistryCategories' => $hasAuMinistryCategories,

            'categories' => Category::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->where('category_type', '!=', 'heading')
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'context',
                    'gst_default',
                ]),

            'remaining' => Transaction::query()
                ->where('household_id', $household->id)
                ->whereNull('category_id')
                ->whereDoesntHave('splits')
                ->count(),

            'deferred' => Transaction::query()
                ->where('household_id', $household->id)
                ->whereNull('category_id')
                ->whereDoesntHave('splits')
                ->whereNotNull('deferred_at')
                ->count(),

            'accounts' => FinancialAccount::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->where('account_type', 'cash')
                ->orderBy('account_name')
                ->get([
                    'id',
                    'account_name',
                    'currency',
                ]),
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

    public function split(
        Request $request,
        Household $household,
        Transaction $transaction
    ): RedirectResponse {
        abort_unless(
            $transaction->household_id === $household->id,
            404
        );

        $validated = $request->validate([
            'splits' => ['required', 'array', 'min:2'],

            'splits.*.type' => [
                'required',
                'in:category,cash',
            ],

            'splits.*.category_id' => [
                'nullable',
                'integer',
            ],

            'splits.*.financial_account_id' => [
                'nullable',
                'integer',
            ],

            'splits.*.amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'splits.*.description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $splitTotal = collect($validated['splits'])
            ->sum(fn($split) => (float) $split['amount']);

        $transactionAmount = abs((float) $transaction->amount);

        if (abs($splitTotal - $transactionAmount) > 0.005) {
            throw ValidationException::withMessages([
                'splits' => 'The split amounts must equal the transaction amount.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $transaction,
            $household
        ) {
            foreach ($validated['splits'] as $split) {

                $signedAmount = $transaction->amount < 0
                    ? -abs((float) $split['amount'])
                    : abs((float) $split['amount']);

                if ($split['type'] === 'category') {
                    $category = Category::query()
                        ->where('household_id', $household->id)
                        ->where('id', $split['category_id'])
                        ->where('category_type', '!=', 'heading')
                        ->first();
                    if (! $category) {
                        dd('Category not found', $split);
                    }

                    $transaction->splits()->create([
                        'category_id' => $category->id,
                        'financial_account_id' => null,
                        'amount' => $signedAmount,
                        'description' => $split['description'] ?? null,
                    ]);

                    if ($category->tracks_balance) {
                        $category->increment(
                            'current_balance',
                            $signedAmount
                        );
                    }
                }

                if ($split['type'] === 'cash') {
                    $cashAccount = FinancialAccount::query()
                        ->where('household_id', $household->id)
                        ->where('id', $split['financial_account_id'])
                        ->where('account_type', 'cash')
                        ->first();

                    if (! $cashAccount) {
                        dd('Cash account not found', $split);
                    }

                    $transaction->splits()->create([
                        'category_id' => null,
                        'financial_account_id' => $cashAccount->id,
                        'amount' => $signedAmount,
                        'description' => $split['description'] ?? 'Cash out',
                    ]);
                }
            }

            $transaction->update([
                'deferred_at' => null,
            ]);
        });

        return redirect()->route(
            'households.transactions.assign',
            $household
        );
    }

    public function storeCash(
        Request $request,
        Household $household
    ): RedirectResponse {
        $validated = $request->validate([
            'transaction_date' => [
                'required',
                'date',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'payee' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'category_id' => [
                'required',
                'integer',
            ],

            'gst_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $category = Category::query()
            ->where('household_id', $household->id)
            ->where('id', $validated['category_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $cashAccount = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->where('account_type', 'cash')
            ->where('currency', 'AUD')
            ->where('is_active', true)
            ->firstOrFail();

        Transaction::create([
            'household_id' => $household->id,
            'financial_account_id' => $cashAccount->id,
            'category_id' => $category->id,

            'transaction_date' =>
            $validated['transaction_date'],

            'payee' =>
            $validated['payee'] ?? null,

            'description' =>
            $validated['description'] ?? null,

            // Spending cash is an outgoing transaction.
            'amount' =>
            -abs((float) $validated['amount']),

            'currency' =>
            $cashAccount->currency,

            'gst_amount' =>
            $validated['gst_amount'] ?? null,
        ]);

        return back();
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
            'gst_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'lte:' . abs((float) $transaction->amount),
            ],
        ]);

        $oldCategoryId = $transaction->category_id;

        $newCategory = Category::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['category_id']);

        $gstAmount = null;

        if ($newCategory->context === 'ministry_au') {
            $gstAmount = $validated['gst_amount'] ?? null;
        }

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
        }

        $transaction->update([
            'category_id' => $newCategory->id,
            'gst_amount' => $gstAmount,
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
