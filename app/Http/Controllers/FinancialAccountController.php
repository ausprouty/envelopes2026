<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FinancialAccount;
use App\Models\Household;
use App\Models\TransactionImportProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FinancialAccountController extends Controller
{
    public function index(Household $household): Response
    {
        // Make sure the logged-in user belongs to this household.
        // Admins are allowed to view any household.
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (
            $user->role !== 'admin'
            && ! $user->households()->whereKey($household->id)->exists()
        ) {
            abort(403);
        }

        return Inertia::render('households/accounts/Index', [
            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],

            'accounts' => FinancialAccount::where('household_id', $household->id)
                ->orderBy('account_name')
                ->get([
                    'id',
                    'account_name',
                    'institution_name',
                    'account_type',
                    'currency',
                    'account_reference',
                    'warning_balance',
                    'is_active',
                ]),
        ]);
    }

    public function create(
        Request $request,
        Household $household
    ): Response {


        return Inertia::render('households/accounts/Edit', [
            'household' => $household,
            'account' => null,
        ]);
    }

    public function store(
        Request $request,
        Household $household
    ): RedirectResponse {


        $validated = $this->validateAccount($request);

        $household->financialAccounts()->create($validated);

        return redirect()
            ->route('households.accounts.index', $household);
    }

    public function edit(
        Household $household,
        FinancialAccount $financialAccount
    ): Response {
        abort_unless(
            $financialAccount->household_id === $household->id,
            404
        );

        $financialAccount->load('importProfiles');

        return Inertia::render('households/accounts/Edit', [
            'account' => $financialAccount,

            'household' => [
                'id' => $household->id,
                'household_name' => $household->household_name,
                'default_currency' => $household->default_currency,
            ],

            'importProfiles' => TransactionImportProfile::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'format',
                ]),

            'selectedImportProfileIds' => $financialAccount
                ->importProfiles
                ->pluck('id')
                ->values(),
        ]);
    }
public function update(
    Request $request,
    Household $household,
    FinancialAccount $financialAccount
): RedirectResponse {
    abort_unless(
        $financialAccount->household_id === $household->id,
        404
    );

    $validated = $this->validateAccount($request);

    $importProfileIds = $validated['import_profile_ids'] ?? [];

    unset($validated['import_profile_ids']);

    $financialAccount->update($validated);

    $financialAccount
        ->importProfiles()
        ->sync($importProfileIds);

    return redirect()
        ->route('households.accounts.index', $household);
}

    private function validateAccount(Request $request): array
    {
        return $request->validate([
            'account_name' => ['required', 'string', 'max:150'],

            'account_reference' => ['nullable', 'string', 'max:100'],

            'account_type' => [
                'required',
                'in:cash,checking,savings,credit_card,term_deposit,investment,retirement,superannuation,crypto,reimbursement,ministry,virtual,other',
            ],

            'available_for_spending' => ['boolean'],

            'closed_at' => ['nullable', 'date'],

            'credit_limit' => ['nullable', 'numeric'],

            'currency' => ['required', 'string', 'size:3'],

            'display_order' => ['integer'],

            'import_profile_ids' => [
                'array',
            ],

            'import_profile_ids.*' => [
                'integer',
                'exists:transaction_import_profiles,id',
            ],

            'include_in_net_worth' => ['boolean'],

            'institution_name' => ['nullable', 'string', 'max:100'],

            'is_active' => ['boolean'],

            'legacy_paidby_id' => ['nullable', 'integer'],

            'transaction_import_profile_id' => [
                'nullable',
                'integer',
                'exists:transaction_import_profiles,id',
            ],

            'warning_balance' => ['nullable', 'numeric'],

            'website' => ['nullable', 'url', 'max:255'],
        ]);
    }
}
