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
    public function create(Household $household): Response
    {
        $accounts = $household->financialAccounts()
            ->where('is_active', true)
            ->orderBy('institution_name')
            ->orderBy('account_name')
            ->get([
                'id',
                'account_name',
                'institution_name',
                'account_type',
                'category_type',
                'currency',
            ]);

        return Inertia::render('households/balances/Create', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
            ],

            'accounts' => $accounts,
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

        return redirect()
            ->route('households.balances.create', [
                'household' => $household->id,
            ])
            ->with('success', 'Balance recorded.');
    }
}
