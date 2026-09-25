<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\FinancialAccountBalanceHistory;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\TransactionImportProfile;
use App\Services\TransactionImport\QfxParser;
use App\Services\TransactionImport\PayeeCleaner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TransactionImportController extends Controller
{

    public function checkDuplicates(
        Request $request,
        Household $household
    ): JsonResponse {
        $validated = $request->validate([
            'financial_account_id' => [
                'required',
                'integer',
                'exists:financial_accounts,id',
            ],

            'transactions' => [
                'required',
                'array',
            ],

            'transactions.*.transaction_date' => [
                'required',
                'date',
            ],

            'transactions.*.description' => [
                'required',
                'string',
            ],

            'transactions.*.amount' => [
                'required',
                'numeric',
            ],

            'transactions.*.external_id' => [
                'nullable',
                'string',
            ],
        ]);

        $account = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['financial_account_id']);

        $transactions = collect($validated['transactions'])
            ->map(function (array $transaction) use ($account) {

                /*
             * QFX:
             * If the bank supplied a FITID, use that as our
             * strongest duplicate identifier.
             */
                if (! empty($transaction['external_id'])) {
                    $importHash =
                        'qfx:' . $transaction['external_id'];
                } else {
                    /*
                 * CSV:
                 * Build our own repeatable hash.
                 */
                    $importHash = hash(
                        'sha256',
                        implode('|', [
                            $account->id,
                            $transaction['transaction_date'],
                            trim($transaction['description']),
                            number_format(
                                (float) $transaction['amount'],
                                2,
                                '.',
                                ''
                            ),
                        ])
                    );
                }

                return [
                    'transaction_date' =>
                    $transaction['transaction_date'],

                    'description' =>
                    trim($transaction['description']),

                    'amount' =>
                    (float) $transaction['amount'],

                    'external_id' =>
                    $transaction['external_id'] ?? null,

                    'import_hash' =>
                    $importHash,
                ];
            });

        $existingHashes = [];

        foreach ($transactions as $transaction) {

            /*
         * QFX duplicate check
         */
            if ($transaction['external_id']) {
                $exists = Transaction::query()
                    ->where(
                        'financial_account_id',
                        $account->id
                    )
                    ->where(
                        'external_id',
                        $transaction['external_id']
                    )
                    ->exists();

                if ($exists) {
                    $existingHashes[] =
                        $transaction['import_hash'];
                }

                continue;
            }

            /*
         * CSV duplicate check
         */
            $exists = Transaction::query()
                ->where(
                    'financial_account_id',
                    $account->id
                )
                ->where(
                    'import_hash',
                    $transaction['import_hash']
                )
                ->exists();

            if ($exists) {
                $existingHashes[] =
                    $transaction['import_hash'];
            }
        }

        return response()->json([
            'transactions' => $transactions->values(),
            'existing_hashes' => $existingHashes,
        ]);
    }

    public function create(
        Household $household
    ): Response {
        return Inertia::render('households/transactions/Import', [
            'household' => $household,

            'accounts' => FinancialAccount::query()
                ->with([
                    'importProfiles' => function ($query) {
                        $query->orderBy('name');
                    },
                ])
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->orderBy('account_name')
                ->get([
                    'id',
                    'account_name',
                    'institution_name',
                    'currency',
                ]),
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
                        fn($query) => $query->where(
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

    public function previewOfx(
        Request $request,
        Household $household,
        PayeeCleaner $descriptionCleaner,
        QfxParser $qfxParser
    ): JsonResponse {
        $validated = $request->validate([
            'financial_account_id' => [
                'required',
                'integer',
                'exists:financial_accounts,id',
            ],

            'ofx_file' => [
                'required',
                'file',
                'extensions:qfx,qbo,ofx,txt',
                'max:10240',
            ],
        ]);

        $account = FinancialAccount::query()
            ->with('importProfiles')
            ->where('household_id', $household->id)
            ->findOrFail($validated['financial_account_id']);

        $profile = $account->importProfiles
            ->firstWhere('format', 'ofx');

        if (! $profile) {
            return response()->json([
                'message' => 'This account does not have an OFX import profile assigned.',
            ], 422);
        }

        $contents = file_get_contents(
            $request->file('ofx_file')->getRealPath()
        );

        $transactions = $qfxParser->parse(
            $contents,
            $profile->description_field ?? 'MEMO',

        );

        $balances = $qfxParser->parseBalances($contents);

        $transactions = collect($transactions)
            ->map(function (array $transaction) use (
                $account,
                $descriptionCleaner
            ) {
                return [
                    'transaction_date' =>
                    $transaction['transaction_date'],

                    'description' =>
                    $descriptionCleaner->cleanWestpac(
                        $transaction['description'] ?? ''
                    ),

                    'amount' =>
                    $transaction['amount'],

                    'currency' =>
                    $account->currency,

                    'external_id' =>
                    $transaction['external_id'],
                ];
            })
            ->values();

        return response()->json([
            'available_balance' => $balances['available_balance'],
            'balance_as_of' => $balances['balance_as_of'],
            'ledger_balance' => $balances['ledger_balance'],
            'transactions' => $transactions,
        ]);
    }

    public function recentTransactions(
        Household $household,
        FinancialAccount $financialAccount
    ) {
        abort_unless(
            $financialAccount->household_id === $household->id,
            404
        );

        return Transaction::query()
            ->where('household_id', $household->id)
            ->where('financial_account_id', $financialAccount->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get([
                'id',
                'transaction_date',
                'description',
                'description',
                'amount',
                'currency',
            ]);
    }

    public function store(
        Request $request,
        Household $household
    ) {
        $validated = $request->validate([
            /*
     * ---------------------------------------------------------
     * Account-level balance information
     * ---------------------------------------------------------
     *
     * OFX / QFX / QBO files can contain account-level balance
     * information in blocks such as LEDGERBAL and AVAILBAL.
     *
     * These values describe the account as a whole and are NOT
     * attached to an individual transaction.
     *
     * CSV files usually do not use these fields. Instead, a CSV
     * may provide a running balance on every transaction row;
     * those values are validated further below as
     * transactions.*.ledger_balance and
     * transactions.*.available_balance.
     */

            'available_balance' => [
                'nullable',
                'numeric',
            ],

            'balance_as_of' => [
                'nullable',
                'date',
            ],

            'ledger_balance' => [
                'nullable',
                'numeric',
            ],

            /*
     * ---------------------------------------------------------
     * Financial account being imported into
     * ---------------------------------------------------------
     */

            'financial_account_id' => [
                'required',
                'integer',
            ],

            /*
     * ---------------------------------------------------------
     * Imported transactions
     * ---------------------------------------------------------
     *
     * Each transaction is normalized into the same structure
     * before it reaches this method, regardless of whether it
     * came from CSV, OFX, QFX or QBO.
     */

            'transactions' => [
                'required',
                'array',
            ],

            'transactions.*.transaction_date' => [
                'required',
                'date',
            ],

            /*
     * Description is the text supplied by the bank.
     *
     * This used to be called "payee" in the transactions table.
     * We renamed it because the bank-supplied text often contains
     * much more than just the merchant/payee name.
     */
            'transactions.*.description' => [
                'required',
                'string',
            ],

            /*
     * Details are entered by the user.
     *
     * This is where we record information we want to remember
     * about the transaction or include on a reimbursement.
     * Imported transactions normally begin with this blank.
     */
            'transactions.*.details' => [
                'nullable',
                'string',
            ],

            'transactions.*.amount' => [
                'required',
                'numeric',
            ],

            'transactions.*.currency' => [
                'required',
                'string',
                'size:3',
            ],

            /*
     * OFX/QFX/QBO transactions normally have an external FITID.
     * CSV transactions often do not, so this is optional.
     */
            'transactions.*.external_id' => [
                'nullable',
                'string',
            ],

            /*
     * ---------------------------------------------------------
     * Transaction-level balance information
     * ---------------------------------------------------------
     *
     * Some CSV exports, including Westpac, provide a running
     * ledger balance on every transaction row.
     *
     * We deliberately preserve these values through the preview
     * and submit process so storeBalanceHistory() can inspect the
     * entire imported batch.
     *
     * From these transaction-level balances we keep:
     *
     *   - the latest observed balance in each calendar month
     *     (balance_type = month_end_observed)
     *
     *   - the highest observed ledger balance in each calendar
     *     year (balance_type = annual_maximum)
     *
     * We must NOT rely on the order of rows in the bank file.
     * Westpac, for example, exports newest transactions first.
     */

            'transactions.*.ledger_balance' => [
                'nullable',
                'numeric',
            ],

            'transactions.*.available_balance' => [
                'nullable',
                'numeric',
            ],
        ]);



        $account = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['financial_account_id']);

        $this->storeBalanceHistory(
            $account,
            $validated['transactions']
        );

        $balanceUpdates = [];

        if (($validated['available_balance'] ?? null) !== null) {
            $balanceUpdates['available_balance'] =
                $validated['available_balance'];
        }

        if (($validated['balance_as_of'] ?? null) !== null) {
            $balanceUpdates['balance_as_of'] =
                $validated['balance_as_of'];
        }

        if (($validated['ledger_balance'] ?? null) !== null) {
            $balanceUpdates['ledger_balance'] =
                $validated['ledger_balance'];
        }

        if ($balanceUpdates !== []) {
            $account->update($balanceUpdates);
        }

        $imported = 0;
        $skipped = 0;

        foreach ($validated['transactions'] as $transaction) {
            $externalId = $transaction['external_id'] ?? null;

            if ($externalId) {
                $alreadyExists = Transaction::query()
                    ->where('financial_account_id', $account->id)
                    ->where('external_id', $externalId)
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;

                    continue;
                }

                $hash = 'qfx:' . $externalId;
                $importSource = 'qfx';
            } else {
                $hash = hash('sha256', implode('|', [
                    $account->id,
                    $transaction['transaction_date'],
                    number_format(
                        (float) $transaction['amount'],
                        2,
                        '.',
                        ''
                    ),
                    mb_strtolower(trim($transaction['description'])),
                ]));

                $alreadyExists = Transaction::query()
                    ->where('financial_account_id', $account->id)
                    ->where('import_hash', $hash)
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;

                    continue;
                }

                $importSource = 'csv-paste';
            }

            Transaction::create([
                'household_id' => $household->id,
                'financial_account_id' => $account->id,
                'category_id' => null,

                'transaction_date' =>
                $transaction['transaction_date'],

                'posted_date' =>
                $transaction['transaction_date'],

                'description' =>
                trim($transaction['description']),

                'amount' =>
                $transaction['amount'],

                'currency' =>
                strtoupper($transaction['currency']),

                'external_id' =>
                $externalId,

                'import_source' =>
                $importSource,

                'import_hash' =>
                $hash,

                'comment' =>
                null,
            ]);

            $imported++;
        }

        return response()->json([
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }

    // Private methods

    private function storeBalanceHistory(
        FinancialAccount $account,
        array $transactions
    ): void {
        $transactionsWithBalances = collect($transactions)
            ->filter(
                fn(array $transaction) =>
                ! empty($transaction['transaction_date'])
                    && (
                        ($transaction['ledger_balance'] ?? null) !== null
                        || ($transaction['available_balance'] ?? null) !== null
                    )
            );

        if ($transactionsWithBalances->isEmpty()) {
            return;
        }

        /*
     * ---------------------------------------------------------
     * Latest observed balance in each calendar month.
     * ---------------------------------------------------------
     *
     * Banks may return transactions newest-first or oldest-first,
     * so we explicitly sort by transaction date.
     *
     * This is the latest balance we observed in the month.
     * It is not necessarily a formal month-end statement balance.
     */
        $monthlyBalances = $transactionsWithBalances
            ->groupBy(
                fn(array $transaction) =>
                substr($transaction['transaction_date'], 0, 7)
            )
            ->map(
                fn($monthTransactions) =>
                $monthTransactions
                    ->sortByDesc('transaction_date')
                    ->first()
            );

        foreach ($monthlyBalances as $transaction) {
            $this->storeMonthEndObservedBalance(
                $account,
                $transaction
            );
        }

        /*
     * ---------------------------------------------------------
     * Highest observed ledger balance in each calendar year.
     * ---------------------------------------------------------
     *
     * This is useful for annual reporting because the highest
     * balance may occur on any transaction, not at month end.
     */
        $annualMaximums = $transactionsWithBalances
            ->filter(
                fn(array $transaction) => ($transaction['ledger_balance'] ?? null) !== null
            )
            ->groupBy(
                fn(array $transaction) =>
                substr($transaction['transaction_date'], 0, 4)
            )
            ->map(
                fn($yearTransactions) =>
                $yearTransactions
                    ->sortByDesc('ledger_balance')
                    ->first()
            );

        foreach ($annualMaximums as $transaction) {
            $this->storeAnnualMaximumBalance(
                $account,
                $transaction
            );
        }
    }

    private function storeMonthEndObservedBalance(
        FinancialAccount $account,
        array $transaction
    ): void {
        $date = $transaction['transaction_date'];

        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        $existing = FinancialAccountBalanceHistory::query()
            ->where('financial_account_id', $account->id)
            ->where('balance_type', 'month_end_observed')
            ->whereYear('balance_date', $year)
            ->whereMonth('balance_date', $month)
            ->first();

        /*
     * Keep the latest observed balance for this month.
     */
        if (
            $existing
            && $existing->balance_date >= $date
        ) {
            return;
        }

        if ($existing) {
            $existing->delete();
        }

        FinancialAccountBalanceHistory::create([
            'financial_account_id' => $account->id,

            'ledger_balance' =>
            $transaction['ledger_balance'] ?? null,

            'available_balance' =>
            $transaction['available_balance'] ?? null,

            'balance_date' =>
            $date,

            'balance_type' =>
            'month_end_observed',

            'source' =>
            'csv_import',
        ]);
    }

    private function storeAnnualMaximumBalance(
        FinancialAccount $account,
        array $transaction
    ): void {
        $date = $transaction['transaction_date'];
        $year = substr($date, 0, 4);

        $newBalance = $transaction['ledger_balance'] ?? null;

        if ($newBalance === null) {
            return;
        }

        $existing = FinancialAccountBalanceHistory::query()
            ->where('financial_account_id', $account->id)
            ->where('balance_type', 'annual_maximum')
            ->whereYear('balance_date', $year)
            ->first();

        /*
     * Keep the highest ledger balance observed in this
     * calendar year, even across overlapping imports.
     */
        if (
            $existing
            && $existing->ledger_balance !== null
            && (float) $existing->ledger_balance >= (float) $newBalance
        ) {
            return;
        }

        if ($existing) {
            $existing->delete();
        }

        FinancialAccountBalanceHistory::create([
            'financial_account_id' => $account->id,

            'ledger_balance' =>
            $newBalance,

            'available_balance' =>
            $transaction['available_balance'] ?? null,

            'balance_date' =>
            $date,

            'balance_type' =>
            'annual_maximum',

            'source' =>
            'csv_import',
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

            $ledgerBalance = null;

            if ($profile->ledger_balance_column) {
                $ledgerBalance = $this->parseAmount(
                    $row[$profile->ledger_balance_column] ?? null
                );
            }

            $availableBalance = null;

            if ($profile->available_balance_column) {
                $availableBalance = $this->parseAmount(
                    $row[$profile->available_balance_column] ?? null
                );
            }

            $transactions[] = [
                'transaction_date' =>
                $parsedDate->format('Y-m-d'),

                // Bank-supplied transaction description.
                'description' =>
                trim($description),

                // User-entered information is blank during import.
                'details' =>
                '',

                'amount' =>
                $amount,

                'currency' =>
                $account->currency,

                'external_id' =>
                null,

                // Balance information is kept during normalization so that
                // the completed import can determine monthly openings,
                // annual maximums, and year-end balances without depending
                // on the bank's row order.
                'ledger_balance' =>
                $ledgerBalance,

                'available_balance' =>
                $availableBalance,
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

    private function parseCsv(string $csv): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csv));

        $lines = array_values(array_filter(
            $lines,
            fn($line) => trim($line) !== ''
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
            fn($header) => $header !== ''
        ));

        $headerSignature = implode('|', $headers);

        $rows = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);

            // Ignore trailing empty CSV columns.
            $values = array_slice(
                $values,
                0,
                count($headers)
            );

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
}
