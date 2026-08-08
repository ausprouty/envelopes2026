<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Transaction;
use Illuminate\Http\Request;
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
        $this->authorizeHousehold($request, $household);

        $transactions = Transaction::query()
            ->where('household_id', $household->id)
            ->with('financialAccount')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('households/transactions/Index', [
            'household' => $household,
            'transactions' => $transactions,
        ]);
    }

    private function authorizeHousehold(
        Request $request,
        Household $household
    ): void {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        $belongsToHousehold = $user->households()
            ->whereKey($household->id)
            ->exists();

        abort_unless($belongsToHousehold, 403);
    }
}

