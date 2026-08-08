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

    
}
