<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\Household;
use App\Models\TransactionImportProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TransactionImportController extends Controller
{
    public function create(
        Request $request,
        Household $household
    ): Response {


        return Inertia::render('households/transactions/Import', [
            'household' => $household,
            'accounts' => $this->accounts($household),
            'preview' => [],
            'profile' => null,
        ]);
    }

    public function preview(
        Request $request,
        Household $household
    ): Response {


        $validated = $request->validate([
            'financial_account_id' => [
                'required',
                Rule::exists('financial_accounts', 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'household_id',
                            $household->id
                        )
                    ),
            ],
            'csv' => ['required', 'string'],
        ]);

        $account = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['financial_account_id']);

        $parsed = $this->parseCsv($validated['csv']);

        $profile = TransactionImportProfile::query()
            ->where('header_signature', $parsed['header_signature'])
            ->first();

        $preview = [];

        if ($profile) {
            $preview = $this->normalizeRows(
                $parsed['rows'],
                $profile,
                $account
            );
        }

        return Inertia::render('households/transactions/Import', [
            'household' => $household,
            'accounts' => $this->accounts($household),
            'selectedAccountId' => $account->id,
            'csv' => $validated['csv'],
            'profile' => $profile,
            'preview' => $preview,
        ]);
    }

    private function accounts(Household $household)
    {
        return FinancialAccount::query()
            ->where('household_id', $household->id)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get([
                'id',
                'account_name',
                'institution_name',
                'currency',
            ]);
    }

    private function parseCsv(string $csv): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csv));

        $lines = array_values(array_filter(
            $lines,
            fn ($line) => trim($line) !== ''
        ));

        if (count($lines) < 2) {
            return [
                'header_signature' => '',
                'rows' => [],
            ];
        }

        $headers = str_getcsv(array_shift($lines));

        // Remove blank trailing columns.
        $headers = array_values(array_filter(
            array_map('trim', $headers),
            fn ($header) => $header !== ''
        ));

        $headerSignature = implode('|', $headers);

        $rows = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);

            // Ignore trailing empty CSV columns.
            $values = array_slice($values, 0, count($headers));

            if (count($values) !== count($headers)) {
                continue;
            }

            $row = array_combine($headers, $values);

            if ($row !== false) {
                $rows[] = $row;
            }
        }

        return [
            'header_signature' => $headerSignature,
            'rows' => $rows,
        ];
    }

    private function normalizeRows(
        array $rows,
        TransactionImportProfile $profile,
        FinancialAccount $account
    ): array {
        $transactions = [];

        foreach ($rows as $row) {
            $date = $row[$profile->date_column] ?? null;
            $description = $row[$profile->description_column] ?? null;

            if (! $date || ! $description) {
                continue;
            }

            $parsedDate = \DateTime::createFromFormat(
                $profile->date_format,
                trim($date)
            );

            if (! $parsedDate) {
                continue;
            }

            $amount = null;

            if ($profile->amount_column) {
                $amount = $this->parseAmount(
                    $row[$profile->amount_column] ?? null
                );
            } else {
                $debit = $this->parseAmount(
                    $row[$profile->debit_column] ?? null
                );

                $credit = $this->parseAmount(
                    $row[$profile->credit_column] ?? null
                );

                if ($debit !== null && $debit != 0) {
                    $amount = -abs($debit);
                } elseif ($credit !== null) {
                    $amount = abs($credit);
                }
            }

            if ($amount === null) {
                continue;
            }

            $transactions[] = [
                'transaction_date' => $parsedDate->format('Y-m-d'),
                'description' => trim($description),
                'amount' => $amount,
                'currency' => $account->currency,
            ];
        }

        return $transactions;
    }

    private function parseAmount(?string $amount): ?float
    {
        if ($amount === null || trim($amount) === '') {
            return null;
        }

        $amount = str_replace(
            ['$', ',', '(', ')'],
            ['', '', '-', ''],
            trim($amount)
        );

        return is_numeric($amount)
            ? (float) $amount
            : null;
    }
}
