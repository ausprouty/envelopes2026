<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{

    public function linkTransfer(
        Request $request,
        Household $household,
        Transaction $transaction
    ): RedirectResponse {
        abort_unless(
            $transaction->household_id === $household->id,
            404
        );

        $validated = $request->validate([
            'matching_transaction_id' => [
                'required',
                'integer',
                'exists:transactions,id',
            ],
        ]);

        $matchingTransaction = Transaction::query()
            ->where('household_id', $household->id)
            ->findOrFail(
                $validated['matching_transaction_id']
            );

        if (
            $matchingTransaction->id === $transaction->id
            || $matchingTransaction->financial_account_id
            === $transaction->financial_account_id
        ) {
            return back()->withErrors([
                'matching_transaction_id' =>
                'The matching transaction must be from another account.',
            ]);
        }

        if (
            $transaction->transfer_transaction_id !== null
            || $matchingTransaction->transfer_transaction_id !== null
        ) {
            return back()->withErrors([
                'matching_transaction_id' =>
                'One of these transactions is already linked to a transfer.',
            ]);
        }

        if (
            ($transaction->amount < 0 && $matchingTransaction->amount < 0)
            || ($transaction->amount > 0 && $matchingTransaction->amount > 0)
        ) {
            return back()->withErrors([
                'matching_transaction_id' =>
                'Transfer transactions must have opposite signs.',
            ]);
        }

        DB::transaction(function () use (
            $transaction,
            $matchingTransaction
        ) {
            $transaction->update([
                'category_id' => null,
                'transfer_transaction_id' =>
                $matchingTransaction->id,
            ]);

            $matchingTransaction->update([
                'category_id' => null,
                'transfer_transaction_id' =>
                $transaction->id,
            ]);
        });

        return back();
    }

    public function storeTransfer(
        Request $request,
        Household $household,
        Transaction $transaction
    ): RedirectResponse {
        abort_unless(
            $transaction->household_id === $household->id,
            404
        );

        $validated = $request->validate([
            'destination_financial_account_id' => [
                'required',
                'integer',
                'exists:financial_accounts,id',
            ],
        ]);

        $destinationAccount = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->findOrFail(
                $validated['destination_financial_account_id']
            );

        if (
            $destinationAccount->id
            === $transaction->financial_account_id
        ) {
            return back()->withErrors([
                'destination_financial_account_id' =>
                'The destination account must be different from the source account.',
            ]);
        }

        if ($transaction->transfer_transaction_id !== null) {
            return back()->withErrors([
                'transfer' =>
                'This transaction is already linked to a transfer.',
            ]);
        }

        DB::transaction(function () use (
            $household,
            $transaction,
            $destinationAccount
        ) {
            $matchingTransaction = Transaction::create([
                'amount' =>
                -1 * $transaction->amount,

                'category_id' =>
                null,

                'comment' =>
                null,

                'currency' =>
                $destinationAccount->currency,

                'deferred_at' =>
                null,

                'description' =>
                'Transfer from '
                    . $transaction->financialAccount->account_name,

                'details' =>
                null,

                'financial_account_id' =>
                $destinationAccount->id,

                'household_id' =>
                $household->id,

                'import_hash' =>
                null,

                'import_source' =>
                'transfer',

                'posted_date' =>
                $transaction->posted_date
                    ?? $transaction->transaction_date,

                'transaction_date' =>
                $transaction->transaction_date,

                'transfer_transaction_id' =>
                $transaction->id,
            ]);

            $transaction->update([
                'category_id' =>
                null,

                'transfer_transaction_id' =>
                $matchingTransaction->id,
            ]);
        });

        return back();
    }

    public function transferMatches(
        Household $household,
        Transaction $transaction
    ): JsonResponse {
        abort_unless(
            $transaction->household_id === $household->id,
            404
        );

        $transaction->load('financialAccount');

        $startDate = date(
            'Y-m-d',
            strtotime($transaction->transaction_date . ' -3 days')
        );

        $endDate = date(
            'Y-m-d',
            strtotime($transaction->transaction_date . ' +3 days')
        );

        $candidates = Transaction::query()
            ->with('financialAccount')
            ->where('household_id', $household->id)
            ->where(
                'financial_account_id',
                '!=',
                $transaction->financial_account_id
            )
            ->whereBetween(
                'transaction_date',
                [$startDate, $endDate]
            )
            ->whereNull('transfer_transaction_id')
            ->where(
                'amount',
                $transaction->amount < 0 ? '>' : '<',
                0
            )
            ->get();

        $transactionAmount = abs((float) $transaction->amount);
        $transactionCurrency =
            $transaction->financialAccount->currency;

        $matches = $candidates
            ->filter(function (Transaction $candidate) use (
                $transactionAmount,
                $transactionCurrency
            ) {
                $candidateAmount =
                    abs((float) $candidate->amount);

                $candidateCurrency =
                    $candidate->financialAccount->currency;

                /*
             * Same currency:
             * amounts should match.
             */
                if (
                    $candidateCurrency ===
                    $transactionCurrency
                ) {
                    return abs(
                        $candidateAmount -
                            $transactionAmount
                    ) <= 0.01;
                }

                /*
             * AUD <-> USD:
             * allow a plausible conversion range.
             */
                $currencies = [
                    $transactionCurrency,
                    $candidateCurrency,
                ];

                sort($currencies);

                if ($currencies === ['AUD', 'USD']) {
                    $smaller = min(
                        $transactionAmount,
                        $candidateAmount
                    );

                    $larger = max(
                        $transactionAmount,
                        $candidateAmount
                    );

                    if ($larger == 0) {
                        return false;
                    }

                    $ratio = $smaller / $larger;

                    return $ratio >= 0.60
                        && $ratio <= 0.80;
                }

                /*
             * Other currency combinations:
             * do not guess.
             */
                return false;
            })
            ->sortBy(function (Transaction $candidate) use (
                $transaction
            ) {
                return abs(
                    strtotime($candidate->transaction_date) -
                        strtotime($transaction->transaction_date)
                );
            })
            ->values();

        return response()->json([
            'matches' => $matches,
        ]);
    }
}
