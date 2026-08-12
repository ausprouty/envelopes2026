<?php

namespace App\Http\Controllers;

use App\Models\FinancialAccount;
use App\Models\Household;
use App\Models\Transaction;
use App\Models\TransactionImportProfile;
use App\Services\TransactionImport\QfxParser;
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

            'transactions.*.payee' => [
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
                            trim($transaction['payee']),
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

                    'payee' =>
                    trim($transaction['payee']),

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
        Request $request,
        Household $household
    ): Response {
        return Inertia::render('households/transactions/Import', [
            'household' => $household,

            'accounts' => FinancialAccount::query()
                ->where('household_id', $household->id)
                ->where('is_active', true)
                ->orderBy('account_name')
                ->get([
                    'id',
                    'account_name',
                    'institution_name',
                    'currency',
                ]),

            'profiles' => TransactionImportProfile::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'header_signature',
                    'date_column',
                    'description_column',
                    'amount_column',
                    'debit_column',
                    'credit_column',
                    'date_format',
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

    public function previewQfx(
        Request $request,
        Household $household,
        QfxParser $qfxParser
    ): JsonResponse {
        $validated = $request->validate([
            'financial_account_id' => [
                'required',
                'integer',
                'exists:financial_accounts,id',
            ],
            'qfx_file' => [
                'required',
                'file',
                'extensions:qfx,txt',
                'max:10240',
            ],
        ]);

        $account = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['financial_account_id']);

        $contents = file_get_contents(
            $request->file('qfx_file')->getRealPath()
        );

        $transactions = $qfxParser->parse($contents);

        $transactions = collect($transactions)
            ->map(function (array $transaction) use ($account) {
                return [
                    'transaction_date' => $transaction['transaction_date'],
                    'description' => $transaction['description']
                        ?: $transaction['payee']
                        ?: '',
                    'payee' => $transaction['payee'],
                    'amount' => $transaction['amount'],
                    'currency' => $account->currency,
                    'external_id' => $transaction['external_id'],
                ];
            })
            ->values();

        return response()->json([
            'transactions' => $transactions,
        ]);
    }


    public function store(
        Request $request,
        Household $household
    ) {
        $validated = $request->validate([
            'financial_account_id' => ['required', 'integer'],

            'transactions' => ['required', 'array'],

            'transactions.*.transaction_date' => ['required', 'date'],
            'transactions.*.payee' => ['required', 'string'],
            'transactions.*.description' => ['nullable', 'string'],
            'transactions.*.amount' => ['required', 'numeric'],
            'transactions.*.currency' => ['required', 'string', 'size:3'],
            'transactions.*.external_id' => ['nullable', 'string'],
        ]);

        $account = FinancialAccount::query()
            ->where('household_id', $household->id)
            ->findOrFail($validated['financial_account_id']);

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
                    mb_strtolower(trim($transaction['payee'])),
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

                'payee' =>
                trim($transaction['payee']),

                'description' =>
                $transaction['description'] ?? null,

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
