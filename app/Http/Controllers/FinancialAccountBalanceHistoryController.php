<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\FinancialAccountBalanceHistory;
use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinancialAccountBalanceHistoryController extends Controller
{
    public function create(
        Request $request,
        Household $household
    ): Response {
        $accounts = $household->financialAccounts()
            ->where('is_active', true)
            ->orderBy('institution_name')
            ->orderBy('account_name')
            ->get([
                'account_name',
                'account_type',
                'category_type',
                'currency',
                'id',
                'institution_name',


            ]);

        $selectedAccountId = $request->integer('financial_account_id');

        if (! $accounts->contains('id', $selectedAccountId)) {
            $selectedAccountId = null;
        }

        return Inertia::render('households/balances/Create', [
            'accounts' => $accounts,
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
            ],
            'selectedAccountId' => $selectedAccountId,
        ]);
    }

    public function index(Household $household): Response
    {
        $accounts = $household->financialAccounts()
            ->with('latestBalanceHistory')
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();

        return Inertia::render('households/balances/Index', [
            'accounts' => $accounts,
            'household' => [
                'household_name' => $household->household_name,
                'id' => $household->id,
            ],
        ]);
    }

  public function show(
    Household $household,
    FinancialAccount $financialAccount
): Response {
    abort_unless(
        $financialAccount->household_id === $household->id,
        404
    );

    $history = $financialAccount->balanceHistory()
        ->where('balance_date', '>=', now()->subMonths(24)->startOfDay())
        ->get([
            'id',
            'balance_date',
            'ledger_balance',
        ]);

    return Inertia::render('households/balances/Show', [
        'household' => [
            'id' => $household->id,
            'household_name' => $household->household_name,
        ],

        'account' => [
            'id' => $financialAccount->id,
            'account_name' => $financialAccount->account_name,
            'institution_name' => $financialAccount->institution_name,
            'currency' => $financialAccount->currency,
            'account_type' => $financialAccount->account_type,
        ],

        'history' => $history,
    ]);
}

    public function store(
        Request $request,
        Household $household
    ): RedirectResponse {
        $validated = $request->validate([
            'financial_account_id' => [
                'required',
                Rule::exists('financial_accounts', 'id')
                    ->where('household_id', $household->id),
            ],
            'balance_date' => [
                'required',
                'date',
            ],
            'ledger_balance' => [
                'required',
                'numeric',
            ],
        ]);

        $account = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['financial_account_id']);

        $existingBalance = FinancialAccountBalanceHistory::query()
            ->where('financial_account_id', $account->id)
            ->where('balance_date', $validated['balance_date'])
            ->where('balance_type', 'balance_snapshot')
            ->first();

        if ($existingBalance) {
            return back()->withErrors([
                'balance_date' => 'A balance has already been recorded for this account on that date.',
            ])->withInput();
        }

        FinancialAccountBalanceHistory::create([
            'available_balance' => null,
            'balance_date' => $validated['balance_date'],
            'balance_type' => 'balance_snapshot',
            'financial_account_id' => $account->id,
            'ledger_balance' => $validated['ledger_balance'],
            'source' => 'manual',
        ]);

        $account->update([
            'balance_as_of' => $validated['balance_date'],
            'ledger_balance' => $validated['ledger_balance'],

        ]);

        return back()->with(
            'success',
            "Balance recorded for {$account->account_name}."
        );
    }
}
