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

    public function index(
        Household $household
    ): Response {
        $profiles = TransactionImportProfile::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'payee_field',
                'description_field',
                'header_signature',
                'date_column',
                'amount_column',
                'debit_column',
                'credit_column',
                'date_format',
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
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'format' => [
                'required',
                'in:csv,ofx',
            ],

            'header_signature' => [
                'nullable',
                'string',
            ],

            'date_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'amount_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'debit_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'credit_column' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_format' => [
                'nullable',
                'string',
                'max:30',
            ],

            'payee_field' => [
                'nullable',
                'in:NAME,MEMO',
            ],

            'description_field' => [
                'nullable',
                'in:NAME,MEMO',
            ],
        ]);

        TransactionImportProfile::create($validated);

        return redirect()
            ->route(
                'import-profiles.index',
                $household
            );
    }
}
