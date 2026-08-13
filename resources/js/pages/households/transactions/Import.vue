<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Upload } from '@lucide/vue';

import { computed, ref } from 'vue';


const props = defineProps<{
    household: {
        id: number;
        household_name: string;
    };

    accounts: Array<{
        id: number;
        account_name: string;
        institution_name: string | null;
        currency: string;
    }>;

    profiles: Array<{
        id: number;
        name: string;
        header_signature: string | null;
        date_column: string;
        description_column: string;
        amount_column: string | null;
        debit_column: string | null;
        credit_column: string | null;
        date_format: string;
    }>;
}>();


const importType = ref<'csv' | 'ofx'>('csv');

const ofxForm = useForm({
    financial_account_id: '',
    ofx_file: null as File | null,
});

const selectQfxFile = (event: Event) => {
    const input = event.target as HTMLInputElement;

    ofxForm.ofx_file = input.files?.[0] ?? null;
};

const submitQfx = async () => {
    errorMessage.value = '';
    preview.value = [];

    if (!ofxForm.financial_account_id) {
        errorMessage.value = 'Please select an account.';

        return;
    }

    if (!ofxForm.ofx_file) {
        errorMessage.value = 'Please select a OFX file.';

        return;
    }

    const formData = new FormData();

    formData.append(
        'financial_account_id',
        String(ofxForm.financial_account_id)
    );

    formData.append(
        'ofx_file',
        ofxForm.ofx_file
    );

    const response = await fetch(
        `/households/${props.household.id}/transactions/import/ofx/preview`,
        {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') ?? '',
                'Accept': 'application/json',
            },
            body: formData,
        }
    );

    if (!response.ok) {
        const errorData = await response.json();

        console.log('OFX upload error:', errorData);

        errorMessage.value =
            errorData.message ?? 'Unable to read the OFX file.';

        return;
    }

    const data = await response.json();

    preview.value = data.transactions.map(
        (transaction: {
            transaction_date: string;
            description: string;
            amount: number;
            currency: string;
            external_id?: string;
        }) => ({
            ...transaction,
            status: 'new' as const,
        })
    );

    financialAccountId.value =
        Number(ofxForm.financial_account_id);

    await checkDuplicates();

    showImportForm.value = false;
};

const financialAccountId = ref<number | ''>('');
const csv = ref('');
const preview = ref<Array<{
    transaction_date: string;
    payee: string;
    description?: string;
    amount: number;
    currency: string;
    import_hash?: string;
    external_id?: string;
    status?: 'new' | 'duplicate';
}>>([]);

const matchedProfile = ref<(typeof props.profiles)[number] | null>(null);
const showImportForm = ref(true);
const errorMessage = ref('');

const selectedAccount = computed(() =>
    props.accounts.find(
        account => account.id === Number(financialAccountId.value)
    )
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

function parseAmount(value: string | undefined): number | null {
    if (!value || value.trim() === '') {
        return null;
    }

    let cleaned = value.trim();

    const negativeParentheses =
        cleaned.startsWith('(') && cleaned.endsWith(')');

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
    const match = value.trim().match(
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

async function previewCsv() {
    errorMessage.value = '';
    preview.value = [];
    matchedProfile.value = null;

    if (!financialAccountId.value) {
        errorMessage.value = 'Please select an account.';

        return;
    }

    if (!csv.value.trim()) {
        errorMessage.value = 'Please paste CSV data.';

        return;
    }

    const lines = csv.value
        .split(/\r?\n/)
        .map(line => line.trim())
        .filter(line => line !== '');

    if (lines.length < 2) {
        errorMessage.value =
            'The CSV needs a header row and at least one transaction.';

        return;
    }

    const rawHeaders = parseCsvLine(lines[0]);
    const headers = normalizeHeaders(rawHeaders);

    const headerSignature = headers.join('|');

    const profile = props.profiles.find(
        item => item.header_signature === headerSignature
    );

    if (!profile) {
        errorMessage.value =
            `CSV format not recognized. Headers found: ${headerSignature}`;

        return;
    }

    matchedProfile.value = profile;

    const account = selectedAccount.value;

    if (!account) {
        errorMessage.value =
            'The selected account could not be found.';

        return;
    }

    const transactions: Array<{
        transaction_date: string;
        payee: string;
        description?: string;
        amount: number;
        currency: string;
        import_hash?: string;
        external_id?: string;
        status?: 'new' | 'duplicate';
    }> = [];

    for (const line of lines.slice(1)) {
        const rawValues = parseCsvLine(line);

        const values = rawValues.slice(0, headers.length);

        if (values.length !== headers.length) {
            continue;
        }

        const row: Record<string, string> = {};

        headers.forEach((header, index) => {
            row[header] = values[index] ?? '';
        });

        const dateValue = row[profile.date_column];

        const transactionDate = convertDate(dateValue);

        if (!transactionDate) {
            continue;
        }

        const payee =
            row[profile.description_column]?.trim();

        if (!payee) {
            continue;
        }

        let amount: number | null = null;

        if (profile.amount_column) {
            amount = parseAmount(
                row[profile.amount_column]
            );
        } else {
            const debit = profile.debit_column
                ? parseAmount(row[profile.debit_column])
                : null;

            const credit = profile.credit_column
                ? parseAmount(row[profile.credit_column])
                : null;

            if (debit !== null && debit !== 0) {
                amount = -Math.abs(debit);
            } else if (credit !== null) {
                amount = Math.abs(credit);
            }
        }

        if (amount === null) {
            continue;
        }

        transactions.push({
            transaction_date: transactionDate,
            payee,
            description: '',
            amount,
            currency: account.currency,
            import_hash: '',
            status: 'new',
        });
    }

    preview.value = transactions;

    await checkDuplicates();

    if (transactions.length === 0) {
        errorMessage.value =
            'The format was recognized, but no transactions could be parsed.';

        return;
    }

    showImportForm.value = false;
}
async function checkDuplicates() {
    if (!financialAccountId.value || preview.value.length === 0) {
        return;
    }

    const response = await fetch(
        `/households/${props.household.id}/transactions/import/check-duplicates`,
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                financial_account_id: financialAccountId.value,
                transactions: preview.value.map(transaction => ({
                    transaction_date: transaction.transaction_date,
                    payee: transaction.payee,
                    amount: transaction.amount,
                    external_id: transaction.external_id ?? null,
                })),
            }),
        }
    );

    if (!response.ok) {
        errorMessage.value =
            'Unable to check for duplicate transactions.';

        return;
    }

    const data = await response.json();

    const existingHashes = new Set<string>(
        data.existing_hashes ?? []
    );

    preview.value = data.transactions.map(
        (transaction: {
            transaction_date: string;
            payee: string;
            amount: number;
            import_hash: string;
            external_id?: string | null;
        }) => ({
            ...transaction,
            currency: selectedAccount.value?.currency ?? '',
            status: existingHashes.has(transaction.import_hash)
                ? 'duplicate'
                : 'new',
        })
    );
}
async function importTransactions() {
    errorMessage.value = '';

    if (!financialAccountId.value) {
        errorMessage.value = 'Please select an account.';

        return;
    }

    if (newTransactions.value.length === 0) {
        errorMessage.value = 'There are no new transactions to import.';

        return;
    }

    const response = await fetch(
        `/households/${props.household.id}/transactions/import/store`,
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                financial_account_id: financialAccountId.value,
                transactions: newTransactions.value.map(transaction => ({
                    transaction_date: transaction.transaction_date,
                    payee: transaction.payee,
                    description: transaction.description ?? '',
                    amount: transaction.amount,
                    currency: transaction.currency,
                    external_id: transaction.external_id ?? null,
                })),
            }),
        }
    );

    if (!response.ok) {
        errorMessage.value = 'Unable to import transactions.';

        return;
    }



    window.location.href =
        `/households/${props.household.id}/transactions`;
}


</script>

<template>

    <Head title="Import Transactions" />

    <div class="w-full space-y-6 p-6">
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

        <!-- ADD THE NEW CHOICE SECTION HERE -->
        <div>
            <div class="mb-3 text-sm font-medium text-gray-700">
                How would you like to import transactions?
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <button type="button" class="rounded-xl border p-5 text-left transition" :class="importType === 'csv'
                    ? 'border-[#477b67] bg-[#477b67]/5 ring-2 ring-[#477b67]/20'
                    : 'border-gray-200 bg-white hover:border-gray-300'
                    " @click="importType = 'csv'">
                    <div class="text-base font-semibold text-gray-900">
                        CSV Import
                    </div>

                    <div class="mt-1 text-sm text-gray-500">
                        Paste transaction data downloaded from your bank.
                    </div>
                </button>

                <button type="button" class="rounded-xl border p-5 text-left transition" :class="importType === 'ofx'
                    ? 'border-[#477b67] bg-[#477b67]/5 ring-2 ring-[#477b67]/20'
                    : 'border-gray-200 bg-white hover:border-gray-300'
                    " @click="importType = 'ofx'">
                    <div class="text-base font-semibold text-gray-900">
                        OFX Import
                    </div>

                    <div class="mt-1 text-sm text-gray-500">
                        Upload a OFX, QFX or QBO file downloaded from your bank.
                    </div>
                </button>
            </div>
        </div>

        <!-- YOUR EXISTING CSV CARD STARTS HERE -->
        <div v-if="importType === 'csv'" class="rounded-xl border">
            <div class="flex items-center justify-between px-6 py-4">
                <div>


                    <div v-if="matchedProfile" class="mt-1 text-sm text-muted-foreground">
                        Format recognized:
                        <strong>{{ matchedProfile.name }}</strong>
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

            <div v-show="showImportForm" class="space-y-5 border-t p-6">
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Account
                    </label>

                    <select v-model="financialAccountId" class="w-full rounded-md border bg-background px-3 py-2">
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

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Paste CSV
                    </label>

                    <textarea v-model="csv" rows="18"
                        class="w-full rounded-md border bg-background px-3 py-3 font-mono text-sm"
                        placeholder="Paste the CSV copied from your bank here..." />
                </div>

                <div v-if="errorMessage" class="rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-800">
                    {{ errorMessage }}
                </div>

                <button type="button" class="rounded-md bg-primary px-4 py-2 text-primary-foreground"
                    @click="previewCsv">
                    Preview Transactions
                </button>
            </div>
        </div>
        <div v-else class="rounded-xl border">
            <div class="px-6 py-4">
                <div class="font-medium">
                    OFX Import
                </div>

                <div class="mt-1 text-sm text-muted-foreground">
                    Upload a OFX file downloaded from your bank.
                </div>
            </div>

            <div class="border-t px-6 py-6">
                <form @submit.prevent="submitQfx" class="space-y-6">

                    <div>
                        <label for="financial_account_id" class="mb-2 block text-sm font-medium">
                            Account
                        </label>

                        <select id="financial_account_id" v-model="ofxForm.financial_account_id"
                            class="w-full rounded-md border px-3 py-2">
                            <option value="">
                                Select an account
                            </option>

                            <option v-for="account in accounts" :key="account.id" :value="account.id">
                                {{ account.account_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="ofx_file" class="mb-2 block text-sm font-medium">
                            OFX File
                        </label>

                        <input id="ofx_file" type="file" accept=".qfx,.qbo,.ofx,.txt"
                            class="block w-full rounded-md border px-3 py-2" @change="selectQfxFile" />
                    </div>

                    <button type="submit"
                        class="rounded-md bg-[#477b67] px-4 py-2 font-medium text-white hover:opacity-90"
                        :disabled="ofxForm.processing">
                        Preview Transactions
                    </button>
                </form>
            </div>
        </div>

        <div v-if="preview.length" class="overflow-hidden rounded-xl border">
            <div class="flex items-center justify-between border-b bg-muted/40 px-4 py-3">
                <div class="font-medium">
                    Transaction Preview
                </div>
                <div class="text-sm text-muted-foreground">
                    {{ preview.length }} total
                    · {{ newTransactions.length }} new
                    · {{ duplicateTransactions.length }} already imported
                </div>
                <div class="flex items-center justify-between border-b bg-muted/40 px-4 py-3">

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


            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Payee</th>
                            <th class="p-3 text-right">Amount</th>
                            <th class="p-3 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(transaction, index) in preview" :key="index" class="border-b last:border-b-0">
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
                                        currency: transaction.currency || 'AUD',
                                    }).format(Number(transaction.amount))
                                }}
                            </td>
                            <td class="p-3">
                                <span v-if="transaction.status === 'new'" class="font-medium text-emerald-700">
                                    New
                                </span>

                                <span v-else-if="transaction.status === 'duplicate'" class="text-muted-foreground">
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
