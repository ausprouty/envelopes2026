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
                'credit_column',
                'date_column',
                'date_format',
                'debit_column',
                'description_column',
                'description_field',
                'format',
                'header_signature',
                'payee_field',
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

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'payee_field' => [
                'nullable',
                'in:NAME,MEMO',
            ],
        ]);
    }
}
