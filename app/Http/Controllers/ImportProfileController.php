<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\TransactionImportProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ImportProfileController extends Controller
{
    public function create(
        Household $household
    ): Response {
        return Inertia::render(
            'households/import-profiles/Create',
            [
                'household' => $household,
            ]
        );
    }

    public function edit(
        Household $household,
        TransactionImportProfile $importProfile
    ): Response {
        $this->ensureProfileBelongsToHousehold(
            $household,
            $importProfile
        );

        return Inertia::render(
            'households/import-profiles/Edit',
            [
                'household' => $household,
                'profile' => $importProfile,
            ]
        );
    }

    public function index(
        Household $household
    ): Response {
        $profiles = $household
            ->transactionImportProfiles()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'amount_column',
                'available_balance_column',
                'credit_column',
                'date_column',
                'date_format',
                'debit_column',
                'description_column',
                'description_field',
                'format',
                'header_signature',
                'ledger_balance_column',
            ]);

        return Inertia::render(
            'households/import-profiles/Index',
            [
                'household' => $household,
                'profiles' => $profiles,
            ]
        );
    }

    public function store(
        Request $request,
        Household $household
    ): RedirectResponse {
        $validated = $this->validateProfile($request);

        $validated = $this->cleanProfileData($validated);

        $household
            ->transactionImportProfiles()
            ->create($validated);

        return redirect()->route(
            'households.import-profiles.index',
            $household
        );
    }

    public function update(
        Request $request,
        Household $household,
        TransactionImportProfile $importProfile
    ): RedirectResponse {
        $this->ensureProfileBelongsToHousehold(
            $household,
            $importProfile
        );

        $validated = $this->validateProfile($request);

        $validated = $this->cleanProfileData($validated);

        $importProfile->update($validated);

        return redirect()->route(
            'households.import-profiles.index',
            $household
        );
    }

    private function ensureProfileBelongsToHousehold(
        Household $household,
        TransactionImportProfile $importProfile
    ): void {
        abort_unless(
            $importProfile->household_id === $household->id,
            404
        );
    }

    private function validateProfile(
        Request $request
    ): array {
        return $request->validate([
            'amount_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'credit_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_format' => [
                'nullable',
                'string',
                'max:30',
            ],

            'debit_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description_field' => [
                'nullable',
                'in:NAME,MEMO',
            ],

            'format' => [
                'required',
                'in:csv,ofx',
            ],

            'header_signature' => [
                'nullable',
                'string',
            ],
            'ledger_balance_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'available_balance_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

        ]);
    }
    private function cleanProfileData(array $data): array
{
    if ($data['format'] === 'csv') {
        // OFX/QFX/QBO-only setting.
        $data['description_field'] = null;
    }

    if ($data['format'] === 'ofx') {
        // CSV-only settings.
        $data['header_signature'] = null;
        $data['date_column'] = null;
        $data['description_column'] = null;
        $data['amount_column'] = null;
        $data['debit_column'] = null;
        $data['credit_column'] = null;
        $data['ledger_balance_column'] = null;
        $data['available_balance_column'] = null;

        // OFX dates come from DTPOSTED rather than a CSV date format.
        $data['date_format'] = null;
    }

    return $data;
}
}
