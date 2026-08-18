<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryTransfer;
use App\Models\FinancialAccount;
use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        Household $household
    ): Response {
        $context = $request->string('context')->toString();

        if (! in_array($context, ['household', 'ministry_au'], true)) {
            $context = 'household';
        }

        $accounts = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->withMax('transactions', 'transaction_date')
            ->orderBy('display_order')
            ->orderBy('account_name')
            ->get()
            ->map(function (FinancialAccount $account) {
                return [
                    'account_name' => $account->account_name,
                    'available_balance' => $account->available_balance !== null
                        ? (float) $account->available_balance
                        : null,
                    'balance_as_of' => $account->balance_as_of,
                    'currency' => $account->currency,
                    'id' => $account->id,
                    'latest_transaction_date' => $account->transactions_max_transaction_date,
                    'ledger_balance' => $account->ledger_balance !== null
                        ? (float) $account->ledger_balance
                        : null,
                ];
            })
            ->values();

        $hasAuMinistryCategories = Category::query()
            ->where('household_id', $household->id)
            ->where('context', 'ministry_au')
            ->where('is_active', true)
            ->exists();

        $headings = Category::query()
            ->where('household_id', $household->id)
            ->where('context', $context)
            ->where('category_type', 'heading')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function (Category $heading) use ($household, $context) {
                $balance = Category::query()
                    ->where('household_id', $household->id)
                    ->where('context', $context)
                    ->where('parent_category_id', $heading->id)
                    ->where('is_active', true)
                    ->where('tracks_balance', true)
                    ->sum('current_balance');

                return [
                    'id' => $heading->id,
                    'name' => $heading->name,
                    'icon' => $heading->icon,
                    'dashboard_image' => $heading->dashboard_image,
                    'balance' => (float) $balance,
                ];
            })
            ->values();

        $totalAvailable = Category::query()
            ->where('household_id', $household->id)
            ->where('context', $context)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('code', '!=', 'income_pool')
            ->sum('current_balance');

        $watchCategories = Category::query()
            ->where('household_id', $household->id)
            ->where('context', $context)
            ->where('is_active', true)
            ->where('tracks_balance', true)
            ->where('category_type', '!=', 'heading')
            ->where(function ($query) {
                $query
                    ->where('needs_attention', true)
                    ->orWhere('current_balance', '<', 0);
            })
            ->orderByRaw('current_balance < 0 DESC')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'dashboard_image',
                'current_balance',
                'needs_attention',
            ]);

        return Inertia::render('households/dashboard/Index', [
            'accounts' => $accounts,
            'dashboardContext' => $context,
            'hasAuMinistryCategories' => $hasAuMinistryCategories,
            'headings' => $headings,
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],
            'householdRole' => $request->attributes->get('household_role'),
            'totalAvailable' => (float) $totalAvailable,
            'watchCategories' => $watchCategories,

        ]);
    }

    public function category(
        Request $request,
        Household $household,
        Category $category
    ): Response {
        abort_unless(
            $category->household_id === $household->id,
            404
        );

        abort_unless(
            $category->category_type === 'heading',
            404
        );

        $envelopes = Category::query()
            ->where('household_id', $household->id)
            ->where('parent_category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Category $envelope) {
                return [
                    'id' => $envelope->id,
                    'name' => $envelope->name,
                    'dashboard_image' => $envelope->dashboard_image,
                    'balance' => (float) $envelope->current_balance,
                    'needs_attention' => (bool) $envelope->needs_attention,
                ];
            });

        return Inertia::render('households/dashboard/Category', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'dashboard_image' => $category->dashboard_image,
            ],
            'envelopes' => $envelopes,
        ]);
    }

    public function envelope(
        Household $household,
        Category $category
    ): Response {
        abort_unless(
            $category->household_id === $household->id,
            404
        );

        $transfersOut = CategoryTransfer::query()
            ->with('toCategory')
            ->where('household_id', $household->id)
            ->where('from_category_id', $category->id)
            ->get()
            ->map(function (CategoryTransfer $transfer) {
                return [
                    'id' => 'transfer-out-' . $transfer->id,
                    'type' => 'transfer',
                    'date' => $transfer->transfer_date->toDateString(),
                    'payee' => 'Transfer to ' . $transfer->toCategory->name,
                    'description' => $transfer->description,
                    'amount' => -(float) $transfer->amount,
                ];
            });

        $transfersIn = CategoryTransfer::query()
            ->with('fromCategory')
            ->where('household_id', $household->id)
            ->where('to_category_id', $category->id)
            ->get()
            ->map(function (CategoryTransfer $transfer) {
                return [
                    'id' => 'transfer-in-' . $transfer->id,
                    'type' => 'transfer',
                    'date' => $transfer->transfer_date->toDateString(),
                    'payee' => 'Transfer from ' . $transfer->fromCategory->name,
                    'description' => $transfer->description,
                    'amount' => (float) $transfer->amount,
                ];
            });

        $transactions = Transaction::query()
            ->where('household_id', $household->id)
            ->where('category_id', $category->id)
            ->get()
            ->map(function (Transaction $transaction) {
                return [
                    'id' => 'transaction-' . $transaction->id,
                    'type' => 'transaction',
                    'date' => $transaction->transaction_date->toDateString(),
                    'payee' => $transaction->payee,
                    'description' => $transaction->description,
                    'amount' => (float) $transaction->amount,
                ];
            });

        $activity = $transactions
            ->concat($transfersOut)
            ->concat($transfersIn)
            ->sortByDesc('date')
            ->take(20)
            ->values();

        $spentThisMonth = Transaction::query()
            ->where('household_id', $household->id)
            ->where('category_id', $category->id)
            ->whereBetween('transaction_date', [
                now()->startOfMonth()->toDateString(),
                now()->toDateString(),
            ])
            ->where('amount', '<', 0)
            ->sum('amount');

        return Inertia::render('households/dashboard/Envelope', [
            'household' => $household,

            'envelope' => [
                'id' => $category->id,
                'name' => $category->name,
                'context' => $category->context,
                'image' => $category->dashboard_image,
                'current_balance' => (float) $category->current_balance,
                'spent_this_month' => abs((float) $spentThisMonth),
            ],

            'activity' => $activity,
        ]);
    }
}
