<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
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
}
