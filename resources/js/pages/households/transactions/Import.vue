<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Upload } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type ImportType = 'csv' | 'ofx';
type TransactionStatus = 'new' | 'duplicate';

interface DuplicateCheckTransaction {
    amount: number;
    external_id?: string | null;
    import_hash: string;
    payee: string;
    transaction_date: string;
}

interface FinancialAccount {
    account_name: string;
    currency: string;
    id: number;
    import_profiles: ImportProfile[];
    institution_name: string | null;
}

interface Household {
    id: number;
    household_name: string;
}

interface ImportProfile {
    amount_column: string | null;
    credit_column: string | null;
    date_column: string | null;
    date_format: string | null;
    debit_column: string | null;
    description_column: string | null;
    description_field: string | null;
    format: string;
    header_signature: string | null;
    id: number;
    name: string;
    payee_field: string | null;
}

interface PreviewTransaction {
    amount: number;
    currency: string;
    description: string;
    external_id?: string | null;
    import_hash?: string;
    payee: string;
    status?: TransactionStatus;
    transaction_date: string;
}
interface RecentTransaction {
    amount: number;
    currency: string;
    description: string | null;
    id: number;
    payee: string | null;
    transaction_date: string;
}



/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps<{
    accounts: FinancialAccount[];
    household: Household;
}>();

/*
|--------------------------------------------------------------------------
| Import State
|--------------------------------------------------------------------------
*/

const financialAccountId = ref<number | ''>('');
const importType = ref<ImportType>('csv');

const csv = ref('');
const preview = ref<PreviewTransaction[]>([]);

const matchedProfile = ref<ImportProfile | null>(null);

const errorMessage = ref('');
const showImportForm = ref(true);
const recentTransactions = ref<RecentTransaction[]>([]);
const loadingRecentTransactions = ref(false);

const availableBalance = ref<number | null>(null);
const balanceAsOf = ref<string | null>(null);
const ledgerBalance = ref<number | null>(null);

/*
|--------------------------------------------------------------------------
| OFX / QFX Form
|--------------------------------------------------------------------------
*/

const ofxForm = useForm({
    financial_account_id: '',
    ofx_file: null as File | null,
});

/*
|--------------------------------------------------------------------------
| Computed Values
|--------------------------------------------------------------------------
*/

const selectedAccount = computed(() =>
    props.accounts.find(
        account =>
            account.id === Number(financialAccountId.value)
    ) ?? null
);

const selectedAccountForImport = computed(() =>
    selectedAccount.value
);

const supportsCsv = computed(() =>
    selectedAccountForImport.value
        ?.import_profiles
        .some(profile => profile.format === 'csv') ?? false
);

const supportsOfx = computed(() =>
    selectedAccountForImport.value
        ?.import_profiles
        .some(profile => profile.format === 'ofx') ?? false
);

const newTransactions = computed(() =>
    preview.value.filter(
        transaction => transaction.status === 'new'
    )
);

const duplicateTransactions = computed(() =>
    preview.value.filter(
        transaction => transaction.status === 'duplicate'
    )
);

/*
|--------------------------------------------------------------------------
| General Helpers
|--------------------------------------------------------------------------
*/

function csrfToken(): string {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}
function formatDate(value: string): string {
    return new Intl.DateTimeFormat('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(new Date(value));
}

function parseCsvLine(line: string): string[] {
    const values: string[] = [];

    let current = '';
    let insideQuotes = false;

    for (let i = 0; i < line.length; i++) {
        const character = line[i];

        if (character === '"') {
            if (insideQuotes && line[i + 1] === '"') {
                current += '"';
                i++;
            } else {
                insideQuotes = !insideQuotes;
            }

            continue;
        }

        if (character === ',' && !insideQuotes) {
            values.push(current.trim());
            current = '';

            continue;
        }

        current += character;
    }

    values.push(current.trim());

    return values;
}

function normalizeHeaders(headers: string[]): string[] {
    return headers
        .map(header => header.trim())
        .filter(header => header !== '');
}

function parseAmount(
    value: string | undefined
): number | null {
    if (!value || value.trim() === '') {
        return null;
    }

    let cleaned = value.trim();

    const negativeParentheses =
        cleaned.startsWith('(') &&
        cleaned.endsWith(')');

    cleaned = cleaned
        .replace(/\$/g, '')
        .replace(/,/g, '')
        .replace(/[()]/g, '');

    const amount = Number(cleaned);

    if (Number.isNaN(amount)) {
        return null;
    }

    return negativeParentheses
        ? -Math.abs(amount)
        : amount;
}

function convertDate(value: string): string | null {
    const match = value
        .trim()
        .match(
            /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/
        );

    if (!match) {
        return null;
    }

    const month = match[1].padStart(2, '0');
    const day = match[2].padStart(2, '0');
    const year = match[3];

    return `${year}-${month}-${day}`;
}

/*
|--------------------------------------------------------------------------
| Recent Transactions
|--------------------------------------------------------------------------
*/

async function loadRecentTransactions(): Promise<void> {
    recentTransactions.value = [];

    if (!financialAccountId.value) {
        return;
    }

    loadingRecentTransactions.value = true;

    try {
        const response = await fetch(
            `/households/${props.household.id}/accounts/${financialAccountId.value}/recent-transactions`,
            {
                headers: {
                    'Accept': 'application/json',
                },
            }
        );

        if (!response.ok) {
            return;
        }

        recentTransactions.value =
            await response.json();
    } finally {
        loadingRecentTransactions.value = false;
    }
}

watch(
    financialAccountId,
    () => {
        loadRecentTransactions();
    }
);

/*
|--------------------------------------------------------------------------
| OFX / QFX Import
|--------------------------------------------------------------------------
*/

function selectQfxFile(event: Event): void {
    const input = event.target as HTMLInputElement;

    ofxForm.ofx_file =
        input.files?.[0] ?? null;
}

async function submitQfx(): Promise<void> {
    errorMessage.value = '';
    preview.value = [];

    /*
    |----------------------------------------------------------------------
    | Use Main Account Selection
    |----------------------------------------------------------------------
    */

    ofxForm.financial_account_id =
        String(financialAccountId.value);

    /*
    |----------------------------------------------------------------------
    | Validate
    |----------------------------------------------------------------------
    */

    if (!ofxForm.financial_account_id) {
        errorMessage.value =
            'Please select an account.';

        return;
    }

    if (!supportsOfx.value) {
        errorMessage.value =
            'This account does not have an OFX import profile.';

        return;
    }

    if (!ofxForm.ofx_file) {
        errorMessage.value =
            'Please select an OFX, QFX or QBO file.';

        return;
    }

    /*
    |----------------------------------------------------------------------
    | Build Upload
    |----------------------------------------------------------------------
    */

    const formData = new FormData();

    formData.append(
        'financial_account_id',
        ofxForm.financial_account_id
    );

    formData.append(
        'ofx_file',
        ofxForm.ofx_file
    );

    /*
    |----------------------------------------------------------------------
    | Request Preview
    |----------------------------------------------------------------------
    */

    const response = await fetch(
        `/households/${props.household.id}/transactions/import/ofx/preview`,
        {
            method: 'POST',

            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },

            body: formData,
        }
    );

    if (!response.ok) {
        const errorData = await response.json();

        console.log(
            'OFX upload error:',
            errorData
        );

        errorMessage.value =
            errorData.message ??
            'Unable to read the OFX file.';

        return;
    }

    /*
    |----------------------------------------------------------------------
    | Store Preview
    |----------------------------------------------------------------------
    */

    const data = await response.json();

    availableBalance.value =
        data.available_balance ?? null;

    balanceAsOf.value =
        data.balance_as_of ?? null;

    ledgerBalance.value =
        data.ledger_balance ?? null;

    preview.value = data.transactions.map(
        (transaction: PreviewTransaction) => ({
            ...transaction,

            description:
                transaction.description ?? '',

            payee:
                transaction.payee ?? '',

            status: 'new' as const,
        })
    );

    await checkDuplicates();

    showImportForm.value = false;
}

/*
|--------------------------------------------------------------------------
| CSV Import
|--------------------------------------------------------------------------
*/

async function previewCsv(): Promise<void> {
    errorMessage.value = '';
    preview.value = [];
    matchedProfile.value = null;

    /*
    |----------------------------------------------------------------------
    | Validate
    |----------------------------------------------------------------------
    */

    if (!financialAccountId.value) {
        errorMessage.value =
            'Please select an account.';

        return;
    }

    if (!supportsCsv.value) {
        errorMessage.value =
            'This account does not have a CSV import profile.';

        return;
    }

    if (!csv.value.trim()) {
        errorMessage.value =
            'Please paste CSV data.';

        return;
    }

    /*
    |----------------------------------------------------------------------
    | Read CSV
    |----------------------------------------------------------------------
    */

    const lines = csv.value
        .split(/\r?\n/)
        .map(line => line.trim())
        .filter(line => line !== '');

    if (lines.length < 2) {
        errorMessage.value =
            'The CSV needs a header row and at least one transaction.';

        return;
    }

    /*
    |----------------------------------------------------------------------
    | Find Matching Profile For Selected Account
    |----------------------------------------------------------------------
    */

    const rawHeaders =
        parseCsvLine(lines[0]);

    const headers =
        normalizeHeaders(rawHeaders);

    const headerSignature =
        headers.join('|');

    const profile =
        selectedAccount.value?.import_profiles.find(
            item =>
                item.format === 'csv' &&
                item.header_signature === headerSignature
        );

    if (!profile) {
        errorMessage.value =
            `CSV format not recognized for this account. Headers found: ${headerSignature}`;

        return;
    }

    matchedProfile.value = profile;

    /*
    |----------------------------------------------------------------------
    | Find Selected Account
    |----------------------------------------------------------------------
    */

    const account = selectedAccount.value;

    if (!account) {
        errorMessage.value =
            'The selected account could not be found.';

        return;
    }

    /*
    |----------------------------------------------------------------------
    | Verify Required Profile Fields
    |----------------------------------------------------------------------
    */

    if (
        !profile.date_column ||
        !profile.description_column
    ) {
        errorMessage.value =
            'This CSV import profile is missing required column settings.';

        return;
    }

    /*
    |----------------------------------------------------------------------
    | Parse Transactions
    |----------------------------------------------------------------------
    */

    const transactions: PreviewTransaction[] = [];

    for (const line of lines.slice(1)) {
        const rawValues =
            parseCsvLine(line);

        const values =
            rawValues.slice(
                0,
                headers.length
            );

        if (
            values.length !==
            headers.length
        ) {
            continue;
        }

        const row: Record<string, string> = {};

        headers.forEach(
            (header, index) => {
                row[header] =
                    values[index] ?? '';
            }
        );

        /*
        |------------------------------------------------------------------
        | Date
        |------------------------------------------------------------------
        */

        const dateValue =
            row[profile.date_column];

        const transactionDate =
            convertDate(dateValue);

        if (!transactionDate) {
            continue;
        }

        /*
        |------------------------------------------------------------------
        | Payee
        |------------------------------------------------------------------
        */

        const payee =
            row[
                profile.description_column
            ]?.trim();

        if (!payee) {
            continue;
        }

        /*
        |------------------------------------------------------------------
        | Amount
        |------------------------------------------------------------------
        */

        let amount: number | null = null;

        if (profile.amount_column) {
            amount = parseAmount(
                row[
                profile.amount_column
                ]
            );
        } else {
            const debit =
                profile.debit_column
                    ? parseAmount(
                        row[
                        profile.debit_column
                        ]
                    )
                    : null;

            const credit =
                profile.credit_column
                    ? parseAmount(
                        row[
                        profile.credit_column
                        ]
                    )
                    : null;

            if (
                debit !== null &&
                debit !== 0
            ) {
                amount =
                    -Math.abs(debit);
            } else if (
                credit !== null
            ) {
                amount =
                    Math.abs(credit);
            }
        }

        if (amount === null) {
            continue;
        }

        /*
        |------------------------------------------------------------------
        | Add Transaction
        |------------------------------------------------------------------
        */

        transactions.push({
            amount,
            currency: account.currency,
            description: '',
            import_hash: '',
            payee,
            status: 'new',
            transaction_date: transactionDate,
        });
    }

    preview.value = transactions;

    /*
    |----------------------------------------------------------------------
    | Check Result
    |----------------------------------------------------------------------
    */

    if (transactions.length === 0) {
        errorMessage.value =
            'The format was recognized, but no transactions could be parsed.';

        return;
    }

    await checkDuplicates();

    showImportForm.value = false;
}

/*
|--------------------------------------------------------------------------
| Duplicate Checking
|--------------------------------------------------------------------------
*/

async function checkDuplicates(): Promise<void> {
    if (
        !financialAccountId.value ||
        preview.value.length === 0
    ) {
        return;
    }

    const response = await fetch(
        `/households/${props.household.id}/transactions/import/check-duplicates`,
        {
            method: 'POST',

            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },

            body: JSON.stringify({
                available_balance:
                    availableBalance.value,

                balance_as_of:
                    balanceAsOf.value,

                financial_account_id:
                    financialAccountId.value,

                ledger_balance:
                    ledgerBalance.value,

                transactions:
                    preview.value.map(
                        transaction => ({
                            amount:
                                transaction.amount,

                            external_id:
                                transaction.external_id ??
                                null,

                            payee:
                                transaction.payee,

                            transaction_date:
                                transaction.transaction_date,
                        })
                    ),
            }),
        }
    );

    if (!response.ok) {
        errorMessage.value =
            'Unable to check for duplicate transactions.';

        return;
    }

    const data = await response.json();

    const existingHashes =
        new Set<string>(
            data.existing_hashes ?? []
        );

    const checkedTransactions =
        data.transactions as DuplicateCheckTransaction[];

    /*
    |----------------------------------------------------------------------
    | Add Duplicate Information Without Losing Description/Currency
    |----------------------------------------------------------------------
    */

    preview.value = preview.value.map(
        (transaction, index) => {
            const checked =
                checkedTransactions[index];

            if (!checked) {
                return transaction;
            }

            return {
                ...transaction,

                external_id:
                    checked.external_id ??
                    transaction.external_id,

                import_hash:
                    checked.import_hash,

                status:
                    existingHashes.has(
                        checked.import_hash
                    )
                        ? 'duplicate'
                        : 'new',
            };
        }
    );
}

/*
|--------------------------------------------------------------------------
| Store Transactions
|--------------------------------------------------------------------------
*/

async function importTransactions(): Promise<void> {
    errorMessage.value = '';

    /*
    |----------------------------------------------------------------------
    | Validate
    |----------------------------------------------------------------------
    */

    if (!financialAccountId.value) {
        errorMessage.value =
            'Please select an account.';

        return;
    }



    /*
    |----------------------------------------------------------------------
    | Store
    |----------------------------------------------------------------------
    */

    const response = await fetch(
        `/households/${props.household.id}/transactions/import/store`,
        {
            method: 'POST',

            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },

            body: JSON.stringify({
                available_balance:
                    availableBalance.value,

                balance_as_of:
                    balanceAsOf.value,

                financial_account_id:
                    financialAccountId.value,

                ledger_balance:
                    ledgerBalance.value,

                transactions:
                    newTransactions.value.map(
                        transaction => ({
                            amount:
                                transaction.amount,

                            currency:
                                transaction.currency,

                            description:
                                transaction.description ??
                                '',

                            external_id:
                                transaction.external_id ??
                                null,

                            payee:
                                transaction.payee,

                            transaction_date:
                                transaction.transaction_date,
                        })
                    ),
            }),
        }
    );

    if (!response.ok) {
        errorMessage.value =
            'Unable to import transactions.';

        return;
    }

    /*
    |----------------------------------------------------------------------
    | Return to Transactions
    |----------------------------------------------------------------------
    */

    window.location.href =
        `/households/${props.household.id}/transactions`;
}
</script>
<template>

    <Head title="Import Transactions" />

    <div class="w-full space-y-6 p-6">
        <!-- Page Header -->
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#477b67] text-white">
                <Upload class="h-6 w-6" />
            </div>

            <div>
                <h1 class="text-2xl font-semibold">
                    Import Transactions
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Import transactions from your bank.
                </p>
            </div>
        </div>

        <!-- Account -->
        <div>
            <label for="import_account_id" class="mb-2 block text-sm font-medium">
                Account
            </label>

            <select id="import_account_id" v-model="financialAccountId"
                class="w-full rounded-md border border-gray-400 bg-background px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20">
                <option value="">
                    Select an account
                </option>

                <option v-for="account in accounts" :key="account.id" :value="account.id">
                    {{ account.account_name }}

                    <template v-if="account.institution_name">
                        — {{ account.institution_name }}
                    </template>
                </option>
            </select>
        </div>

        <!-- Recent Transactions -->
        <div v-if="financialAccountId" class="overflow-hidden rounded-xl border border-gray-300">
            <div class="border-b border-gray-300 bg-muted/40 px-4 py-3">
                <div class="font-medium">
                    Recently imported into this account
                </div>

                <div class="mt-1 text-sm text-muted-foreground">
                    Check these before importing another file.
                </div>
            </div>

            <div v-if="loadingRecentTransactions" class="p-4 text-sm text-muted-foreground">
                Loading recent transactions...
            </div>

            <div v-else-if="recentTransactions.length === 0" class="p-4 text-sm text-muted-foreground">
                No transactions have been imported into this account yet.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-300 bg-muted">
                        <tr>
                            <th class="p-3 text-left">
                                Date
                            </th>

                            <th class="p-3 text-left">
                                Payee
                            </th>

                            <th class="p-3 text-right">
                                Amount
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="transaction in recentTransactions" :key="transaction.id"
                            class="border-b border-gray-300 last:border-b-0">
                            <td class="whitespace-nowrap p-3">
                                {{ formatDate(transaction.transaction_date) }}
                            </td>

                            <td class="p-3">
                                {{
                                    transaction.payee ||
                                    transaction.description ||
                                    '—'
                                }}
                            </td>

                            <td class="whitespace-nowrap p-3 text-right" :class="transaction.amount < 0
                                ? 'text-red-600'
                                : 'text-emerald-700'
                                ">
                                {{
                                    new Intl.NumberFormat('en-AU', {
                                        style: 'currency',
                                        currency:
                                            transaction.currency || 'AUD',
                                    }).format(
                                        Number(transaction.amount)
                                    )
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Import Method -->
        <div v-if="financialAccountId">
            <div class="mb-3 text-sm font-medium text-gray-700">
                How would you like to import transactions?
            </div>

            <div v-if="supportsCsv || supportsOfx" class="grid gap-4 sm:grid-cols-2">
                <!-- CSV -->
                <button v-if="supportsCsv" type="button" class="rounded-xl border p-5 text-left transition" :class="importType === 'csv'
                    ? 'border-[#477b67] bg-[#477b67]/5 ring-2 ring-[#477b67]/20'
                    : 'border-gray-400 bg-white hover:border-gray-500'
                    " @click="importType = 'csv'">
                    <div class="text-base font-semibold text-gray-900">
                        CSV Import
                    </div>

                    <div class="mt-1 text-sm text-gray-500">
                        Paste transaction data downloaded from your bank.
                    </div>
                </button>

                <!-- OFX / QFX / QBO -->
                <button v-if="supportsOfx" type="button" class="rounded-xl border p-5 text-left transition" :class="importType === 'ofx'
                    ? 'border-[#477b67] bg-[#477b67]/5 ring-2 ring-[#477b67]/20'
                    : 'border-gray-400 bg-white hover:border-gray-500'
                    " @click="importType = 'ofx'">
                    <div class="text-base font-semibold text-gray-900">
                        OFX Import
                    </div>

                    <div class="mt-1 text-sm text-gray-500">
                        Upload an OFX, QFX or QBO file downloaded from your bank.
                    </div>
                </button>
            </div>

            <!-- No Profiles -->
            <div v-if="!supportsCsv && !supportsOfx" class="rounded-md border border-amber-300 bg-amber-50 p-4">
                <div class="font-medium text-amber-900">
                    No import methods are set up for this account yet.
                </div>

                <div class="mt-1 text-sm text-amber-800">
                    You can assign an existing import profile or create a new one.
                </div>

                <div class="mt-3 flex flex-wrap gap-3">
                    <Link :href="`/households/${household.id}/accounts/${financialAccountId}/edit`"
                        class="inline-flex items-center rounded-md bg-[#477b67] px-3 py-2 text-sm font-medium text-white hover:opacity-90">
                        Assign Existing Profile
                    </Link>

                    <Link :href="`/households/${household.id}/import-profiles/create`"
                        class="inline-flex items-center rounded-md border border-gray-400 bg-white px-3 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">
                        Create New Profile
                    </Link>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div v-if="errorMessage" class="rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-800">
            {{ errorMessage }}
        </div>

        <!-- CSV Import -->
        <div v-if="
            financialAccountId &&
            importType === 'csv' &&
            supportsCsv
        " class="rounded-xl border border-gray-300">
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <div class="font-medium">
                        CSV Import
                    </div>

                    <div v-if="matchedProfile" class="mt-1 text-sm text-muted-foreground">
                        Format recognized:
                        <strong>
                            {{ matchedProfile.name }}
                        </strong>
                    </div>
                </div>

                <button v-if="preview.length" type="button"
                    class="flex items-center gap-2 rounded-md px-3 py-2 text-sm hover:bg-muted"
                    @click="showImportForm = !showImportForm">
                    {{
                        showImportForm
                            ? 'Hide pasted CSV'
                            : 'Show pasted CSV'
                    }}

                    <ChevronUp v-if="showImportForm" class="h-4 w-4" />

                    <ChevronDown v-else class="h-4 w-4" />
                </button>
            </div>

            <div v-show="showImportForm" class="space-y-5 border-t border-gray-300 p-6">
                <div>
                    <label for="csv_data" class="mb-2 block text-sm font-medium">
                        Paste CSV
                    </label>

                    <textarea id="csv_data" v-model="csv" rows="18"
                        class="w-full rounded-md border border-gray-400 bg-background px-3 py-3 font-mono text-sm focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20"
                        placeholder="Paste the CSV copied from your bank here..." />
                </div>

                <button type="button" class="rounded-md bg-[#477b67] px-4 py-2 font-medium text-white hover:opacity-90"
                    @click="previewCsv">
                    Preview Transactions
                </button>
            </div>
        </div>

        <!-- OFX / QFX / QBO Import -->
        <div v-if="
            financialAccountId &&
            importType === 'ofx' &&
            supportsOfx
        " class="rounded-xl border border-gray-300">
            <div class="px-6 py-4">
                <div class="font-medium">
                    OFX / QFX / QBO Import
                </div>

                <div class="mt-1 text-sm text-muted-foreground">
                    Upload a transaction file downloaded from your bank.
                </div>
            </div>

            <div class="border-t border-gray-300 px-6 py-6">
                <form class="space-y-6" @submit.prevent="submitQfx">
                    <!-- File -->
                    <div>
                        <label for="ofx_file" class="mb-2 block text-sm font-medium">
                            OFX, QFX or QBO File
                        </label>

                        <input id="ofx_file" type="file" accept=".qfx,.qbo,.ofx,.txt"
                            class="block w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20"
                            @change="selectQfxFile" />
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                        class="rounded-md bg-[#477b67] px-4 py-2 font-medium text-white hover:opacity-90 disabled:opacity-50"
                        :disabled="ofxForm.processing">
                        Preview Transactions
                    </button>
                </form>
            </div>
        </div>

        <!-- Transaction Preview -->
        <div v-if="preview.length" class="overflow-hidden rounded-xl border border-gray-300">
            <!-- Preview Header -->
            <div
                class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-300 bg-muted/40 px-4 py-3">
                <div class="font-medium">
                    Transaction Preview
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-sm text-muted-foreground">
                        {{ preview.length }} total
                        · {{ newTransactions.length }} new
                        · {{ duplicateTransactions.length }} already imported
                    </div>

                    <button v-if="newTransactions.length" type="button"
                        class="rounded-md bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:opacity-90"
                        @click="importTransactions">
                        Import {{ newTransactions.length }} New
                    </button>
                </div>
            </div>

            <!-- Preview Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-300 bg-muted">
                        <tr>
                            <th class="p-3 text-left">
                                Date
                            </th>

                            <th class="p-3 text-left">
                                Payee
                            </th>

                            <th class="p-3 text-right">
                                Amount
                            </th>

                            <th class="p-3 text-left">
                                Status
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(transaction, index) in preview" :key="index"
                            class="border-b border-gray-300 last:border-b-0">
                            <td class="whitespace-nowrap p-3">
                                {{ transaction.transaction_date }}
                            </td>

                            <td class="p-3">
                                {{ transaction.payee }}
                            </td>

                            <td class="whitespace-nowrap p-3 text-right" :class="transaction.amount < 0
                                ? 'text-red-600'
                                : 'text-emerald-700'
                                ">
                                {{
                                    new Intl.NumberFormat('en-AU', {
                                        style: 'currency',
                                        currency:
                                            transaction.currency || 'AUD',
                                    }).format(
                                        Number(transaction.amount)
                                    )
                                }}
                            </td>

                            <td class="p-3">
                                <span v-if="transaction.status === 'new'" class="font-medium text-emerald-700">
                                    New
                                </span>

                                <span v-else-if="
                                    transaction.status ===
                                    'duplicate'
                                " class="text-muted-foreground">
                                    Already imported
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
