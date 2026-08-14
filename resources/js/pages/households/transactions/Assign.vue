<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Tag } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

type Category = {
    id: number;
    name: string;
    context: string;
    gst_default: boolean;
};

type FinancialAccount = {
    id: number;
    account_name: string;
    currency: string;
};

type Transaction = {
    id: number;
    transaction_date: string;
    payee: string | null;
    amount: number | string;
    currency: string;
    description: string | null;
};

type SplitRow = {
    type: 'category' | 'cash';
    category_id: number | null;
    financial_account_id: number | null;
    amount: string;
    description: string;
};

const props = defineProps<{
    household: {
        id: number;
        household_name: string;
    };
    transaction: Transaction | null;
    categories: Category[];
    accounts: FinancialAccount[];
    remaining: number;
    deferred: number;
    showDeferred: boolean;
    hasAuMinistryCategories: boolean;
}>();

// -----------------------------------------------------------------------------
// Transaction
// -----------------------------------------------------------------------------

const description = ref(
    props.transaction?.description ?? '',
);

const amount = computed(() => {
    if (!props.transaction) {
        return '';
    }

    return Number(props.transaction.amount).toLocaleString('en-AU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
});
const enteringCash = ref(false);

const cashDate = ref(
    new Date().toISOString().slice(0, 10),
);

const cashAmount = ref('');

const cashPayee = ref('');

const cashDescription = ref('');

const savingCash = ref(false);

// -----------------------------------------------------------------------------
// Category assignment
// -----------------------------------------------------------------------------

const assignmentContext = ref<'household' | 'ministry_au'>(
    'household',
);

const categoryId = ref<number | ''>('');

const always = ref(false);

const matchText = ref('');

const saving = ref(false);

const filteredCategories = computed(() =>
    props.categories.filter(
        category =>
            category.context === assignmentContext.value,
    ),
);

// -----------------------------------------------------------------------------
// GST
// -----------------------------------------------------------------------------

const calculateGst = ref(false);

const gstAmount = ref('');

const calculateGstAmount = () => {
    if (!props.transaction) {
        gstAmount.value = '';

        return;
    }

    const transactionAmount = enteringCash.value
        ? Math.abs(Number(cashAmount.value))
        : Math.abs(Number(props.transaction?.amount ?? 0));

    gstAmount.value = (
        transactionAmount / 11
    ).toFixed(2);
};

// -----------------------------------------------------------------------------
// Split transaction
// -----------------------------------------------------------------------------



function newSplitRow(): SplitRow {
    return {
        type: 'category',
        category_id: null,
        financial_account_id: null,
        amount: '',
        description: '',
    };
}

const splitting = ref(false);

const splitRows = ref<SplitRow[]>([
    newSplitRow(),
    newSplitRow(),
]);

const transactionAmount = computed(() => {
    return Math.abs(
        Number(props.transaction?.amount ?? 0),
    );
});

const splitTotal = computed(() => {
    return splitRows.value.reduce(
        (total, row) =>
            total + (Number(row.amount) || 0),
        0,
    );
});

const splitRemaining = computed(() => {
    return transactionAmount.value - splitTotal.value;
});

const splitBalanced = computed(() => {
    return Math.abs(splitRemaining.value) < 0.005;
});



// -----------------------------------------------------------------------------
// Watchers
// -----------------------------------------------------------------------------

watch(
    () => props.transaction?.id,
    () => {
        description.value =
            props.transaction?.description ?? '';
    },
);

watch(assignmentContext, () => {
    categoryId.value = '';
    calculateGst.value = false;
    gstAmount.value = '';
});

watch(categoryId, newCategoryId => {
    if (!newCategoryId) {
        calculateGst.value = false;
        gstAmount.value = '';
        return;
    }

    const category = props.categories.find(
        category =>
            category.id === Number(newCategoryId),
    );

    if (
        !category ||
        assignmentContext.value !== 'ministry_au'
    ) {
        calculateGst.value = false;
        gstAmount.value = '';
        return;
    }

    calculateGst.value = category.gst_default;

    if (calculateGst.value) {
        calculateGstAmount();
    } else {
        gstAmount.value = '';
    }
});

watch(calculateGst, enabled => {
    if (enabled) {
        calculateGstAmount();
    } else {
        gstAmount.value = '';
    }
});

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString(
        'en-US',
        {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        },
    );
};

function suggestMatchText() {
    if (!props.transaction?.payee) {
        return '';
    }

    const payee = props.transaction.payee.trim();

    const commonMatches = [
        'SAFEWAY',
        'WINCO',
        'NETFLIX',
        'YOUTUBE TV',
        'KAADY CAR WASHES',
        'APPLE.COM/BILL',
        'AMAZON',
        'COSTCO',
        'UBER',
    ];

    const upperPayee = payee.toUpperCase();

    const match = commonMatches.find(
        item => upperPayee.includes(item),
    );

    return match ?? payee;
}

// -----------------------------------------------------------------------------
// Cash transaction
// -----------------------------------------------------------------------------

function startCashTransaction() {
    enteringCash.value = true;

    cashDate.value = new Date()
        .toISOString()
        .slice(0, 10);

    cashAmount.value = '';
    cashPayee.value = '';
    cashDescription.value = '';

    assignmentContext.value = 'household';
    categoryId.value = '';
    calculateGst.value = false;
    gstAmount.value = '';
}

function cancelCashTransaction() {
    enteringCash.value = false;

    cashAmount.value = '';
    cashPayee.value = '';
    cashDescription.value = '';

    assignmentContext.value = 'household';
    categoryId.value = '';
    calculateGst.value = false;
    gstAmount.value = '';
}

function saveCashTransaction() {
    if (
        !cashDate.value ||
        !cashAmount.value ||
        !categoryId.value
    ) {
        return;
    }

    savingCash.value = true;

    router.post(
        `/households/${props.household.id}/transactions/cash`,
        {
            transaction_date: cashDate.value,
            amount: cashAmount.value,
            payee: cashPayee.value || null,
            description: cashDescription.value || null,
            category_id: categoryId.value,

            gst_amount:
                assignmentContext.value === 'ministry_au' &&
                    calculateGst.value
                    ? gstAmount.value
                    : null,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                cancelCashTransaction();
            },

            onFinish: () => {
                savingCash.value = false;
            },
        },
    );
}

// -----------------------------------------------------------------------------
// Assignment actions
// -----------------------------------------------------------------------------

function toggleAlways() {
    if (always.value && !matchText.value) {
        matchText.value = suggestMatchText();
    }
}

function doLater() {
    if (!props.transaction) {
        return;
    }

    router.post(
        `/households/${props.household.id}/transactions/${props.transaction.id}/defer`,
    );
}

function saveAndNext() {
    if (!props.transaction || !categoryId.value) {
        return;
    }

    saving.value = true;

    router.put(
        `/households/${props.household.id}/transactions/${props.transaction.id}/category`,
        {
            category_id: categoryId.value,
            always: always.value,
            match_type: 'contains',
            match_text: always.value
                ? matchText.value
                : null,
            normalized_payee: null,
            gst_amount:
                assignmentContext.value === 'ministry_au' && calculateGst.value
                    ? gstAmount.value
                    : null,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                categoryId.value = '';
                always.value = false;
                matchText.value = '';
                calculateGst.value = false;
                gstAmount.value = '';
                assignmentContext.value = 'household';
            },

            onFinish: () => {
                saving.value = false;
            },
        },
    );
}

// -----------------------------------------------------------------------------
// Split actions
// -----------------------------------------------------------------------------

function addSplitRow() {
    splitRows.value.push({
        type: 'category',
        category_id: null,
        financial_account_id: null,
        amount: '',
        description: '',
    });
}

function removeSplitRow(index: number) {
    splitRows.value.splice(index, 1);
}

function saveSplit() {
    if (
        !props.transaction ||
        !splitBalanced.value
    ) {
        return;
    }

    router.post(
        `/households/${props.household.id}/transactions/${props.transaction.id}/split`,
        {
            splits: splitRows.value,
        },
    );
}
</script>
<template>

    <Head title="Review Transactions" />

    <div class="mx-auto max-w-3xl space-y-6 p-6">
        <!--  HEADING   -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#477b67] text-white">
                    <Tag class="h-6 w-6" />
                </div>

                <div>
                    <h1 class="text-2xl font-semibold">
                        Assign Transactions
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ household.household_name }}
                    </p>
                </div>
            </div>

            <div class="rounded-full bg-muted px-3 py-1 text-sm text-muted-foreground">
                {{
                    showDeferred
                        ? `${deferred} deferred`
                        : `${remaining} remaining`
                }}
            </div>
        </div>

        <!--  CASH TRANSACTION ENTRY   -->
        <div v-if="enteringCash" class="rounded-2xl border border-gray-300 bg-white p-6 shadow-sm">
            <div class="border-b border-gray-300 pb-5">
                <div class="text-2xl font-semibold text-gray-900">
                    Cash Transaction
                </div>

                <p class="mt-1 text-sm text-muted-foreground">
                    Record something you paid for with cash.
                </p>
            </div>

            <!--  DATE AND AMOUNT   -->
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Date
                    </label>

                    <input v-model="cashDate" type="date"
                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Amount
                    </label>

                    <input v-model="cashAmount" type="number" step="0.01" min="0" placeholder="0.00"
                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />
                </div>
            </div>

            <!--  PERSONAL / MINISTRY   -->
            <div v-if="hasAuMinistryCategories" class="mt-5 flex gap-2">
                <button type="button" class="rounded-md border px-4 py-2 font-medium shadow-sm" :class="assignmentContext === 'household'
                        ? 'border-[#477b67] bg-[#477b67] text-white'
                        : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
                    " @click="assignmentContext = 'household'">
                    Personal
                </button>

                <button type="button" class="rounded-md border px-4 py-2 font-medium shadow-sm" :class="assignmentContext === 'ministry_au'
                        ? 'border-[#477b67] bg-[#477b67] text-white'
                        : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
                    " @click="assignmentContext = 'ministry_au'">
                    Ministry
                </button>
            </div>

            <!--  PAYEE   -->
            <div class="mt-5">
                <label class="mb-2 block text-sm font-medium">
                    Payee
                    <span class="font-normal text-muted-foreground">
                        (optional)
                    </span>
                </label>

                <input v-model="cashPayee" type="text" placeholder="Store or person"
                    class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />
            </div>

            <!--  DESCRIPTION   -->
            <div class="mt-5">
                <label class="mb-2 block text-sm font-medium">
                    Description
                    <span class="font-normal text-muted-foreground">
                        (optional)
                    </span>
                </label>

                <input v-model="cashDescription" type="text" placeholder="Mop"
                    class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />
            </div>

            <!--  CATEGORY   -->
            <div class="mt-5">
                <label class="mb-2 block text-sm font-medium">
                    Category
                </label>

                <select v-model="categoryId"
                    class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200">
                    <option value="">
                        Select a category
                    </option>

                    <option v-for="category in filteredCategories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <!--  GST   -->
            <div v-if="assignmentContext === 'ministry_au'"
                class="mt-5 rounded-lg border border-gray-300 bg-gray-50 p-4">
                <label class="flex items-center gap-3">
                    <input v-model="calculateGst" type="checkbox" class="h-4 w-4" />

                    <span class="text-sm font-medium">
                        Calculate GST
                    </span>
                </label>

                <div v-if="calculateGst" class="mt-4">
                    <label class="mb-2 block text-sm font-medium">
                        GST Amount
                    </label>

                    <input v-model="gstAmount" type="number" step="0.01" min="0"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />

                    <p class="mt-1 text-sm text-muted-foreground">
                        Calculated as 1/11 of the transaction.
                        You can change it if needed.
                    </p>
                </div>
            </div>

            <!--  CASH BUTTONS   -->
            <div class="mt-6 flex items-center gap-3">
                <button type="button" :disabled="!cashDate ||
                    !cashAmount ||
                    !categoryId ||
                    savingCash
                    "
                    class="inline-flex items-center gap-2 rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white shadow-sm hover:bg-[#3d6b59] disabled:opacity-50"
                    @click="saveCashTransaction">
                    <Check class="h-4 w-4" />

                    {{
                        savingCash
                            ? 'Saving...'
                            : 'Save Cash Transaction'
                    }}
                </button>

                <button type="button"
                    class="rounded-md border border-gray-300 bg-gray-50 px-5 py-2.5 font-medium text-gray-700 shadow-sm hover:bg-gray-100"
                    @click="cancelCashTransaction">
                    Cancel
                </button>
            </div>
        </div>

        <!--  NORMAL TRANSACTION   -->
        <div v-else-if="transaction" class="rounded-2xl border border-gray-300 bg-white p-6 shadow-sm">
            <!--  TRANSACTION HEADER   -->
            <div class="border-b border-gray-300 pb-5">
                <div class="flex items-start justify-between gap-4">
                    <!--  DATE AND AMOUNT   -->
                    <div>
                        <div class="text-sm text-gray-500">
                            {{ formatDate(transaction.transaction_date) }}
                        </div>

                        <div class="mt-2 text-xl font-semibold" :class="Number(transaction.amount) < 0
                                ? 'text-red-600'
                                : 'text-emerald-700'
                            ">
                            {{ transaction.currency }}
                            {{ amount }}
                        </div>
                    </div>

                    <!--  CASH TRANSACTION BUTTON   -->
                    <button type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="startCashTransaction">
                        Cash Transaction
                    </button>
                </div>

                <!--  PAYEE   -->
                <div class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ transaction.payee || 'Unknown payee' }}
                </div>
            </div>

            <!--  PERSONAL / MINISTRY   -->
            <div v-if="hasAuMinistryCategories" class="mt-5 flex gap-2">
                <button type="button" class="rounded-md border px-4 py-2 font-medium shadow-sm" :class="assignmentContext === 'household'
                        ? 'border-[#477b67] bg-[#477b67] text-white'
                        : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
                    " @click="assignmentContext = 'household'">
                    Personal
                </button>

                <button type="button" class="rounded-md border px-4 py-2 font-medium shadow-sm" :class="assignmentContext === 'ministry_au'
                        ? 'border-[#477b67] bg-[#477b67] text-white'
                        : 'border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100'
                    " @click="assignmentContext = 'ministry_au'">
                    Ministry
                </button>
            </div>

            <!--  GST   -->
            <div v-if="assignmentContext === 'ministry_au'"
                class="mt-5 rounded-lg border border-gray-300 bg-gray-50 p-4">
                <label class="flex items-center gap-3">
                    <input v-model="calculateGst" type="checkbox" class="h-4 w-4" />

                    <span class="text-sm font-medium">
                        Calculate GST
                    </span>
                </label>

                <div v-if="calculateGst" class="mt-4">
                    <label class="mb-2 block text-sm font-medium">
                        GST Amount
                    </label>

                    <input v-model="gstAmount" type="number" step="0.01" min="0"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />

                    <p class="mt-1 text-sm text-muted-foreground">
                        Calculated as 1/11 of the transaction.
                        You can change it if needed.
                    </p>
                </div>
            </div>

            <!--  DESCRIPTION   -->
            <div class="mt-5">
                <label for="description" class="mb-2 block text-sm font-medium">
                    Description
                </label>

                <input id="description" v-model="description" type="text" placeholder="Add a description"
                    class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />
            </div>

            <!--  CATEGORY   -->
            <div class="space-y-5 pt-5">
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Category
                    </label>

                    <select v-model="categoryId"
                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200">
                        <option value="">
                            Select a category
                        </option>

                        <option v-for="category in filteredCategories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>

                <!--  ALWAYS USE THIS CATEGORY   -->
                <label class="flex items-center gap-3">
                    <input v-model="always" type="checkbox" class="h-4 w-4" @change="toggleAlways" />

                    <span class="text-sm font-medium">
                        Always use this category for this payee
                    </span>
                </label>

                <div v-if="always">
                    <label class="mb-2 block text-sm font-medium">
                        When payee contains
                    </label>

                    <input v-model="matchText" type="text"
                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />

                    <p class="mt-1 text-sm text-muted-foreground">
                        You can shorten this to the stable part of the payee,
                        such as SAFEWAY or NETFLIX.
                    </p>
                </div>

                <!--  BUTTON ROW   -->
                <div class="flex items-center gap-3">
                    <button type="button" :disabled="!categoryId || saving"
                        class="inline-flex items-center gap-2 rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white shadow-sm hover:bg-[#3d6b59] disabled:opacity-50"
                        @click="saveAndNext">
                        <Check class="h-4 w-4" />

                        {{
                            saving
                                ? 'Saving...'
                                : 'Save & Next'
                        }}
                    </button>

                    <button type="button"
                        class="rounded-md border border-gray-300 bg-gray-50 px-5 py-2.5 font-medium text-gray-700 shadow-sm hover:bg-gray-100"
                        @click="splitting = !splitting">
                        Split Transaction
                    </button>

                    <button type="button"
                        class="rounded-md border border-gray-300 bg-gray-50 px-5 py-2.5 font-medium text-gray-700 shadow-sm hover:bg-gray-100"
                        @click="doLater">
                        Do Later
                    </button>
                </div>

                <!--  SPLITTING TRANSACTIONS   -->
                <div v-if="splitting" class="mt-6 rounded-xl border border-gray-300 bg-gray-50/40 p-4">
                    <div class="mb-4 text-lg font-semibold">
                        Split Transaction
                    </div>

                    <div v-for="(row, index) in splitRows" :key="index"
                        class="mb-3 grid gap-3 md:grid-cols-[1fr_140px_1fr_auto]">
                        <div class="grid gap-2">
                            <select v-model="row.type"
                                class="rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200">
                                <option value="category">
                                    Category
                                </option>

                                <option value="cash">
                                    Cash Out
                                </option>
                            </select>

                            <select v-if="row.type === 'category'" v-model="row.category_id"
                                class="rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200">
                                <option :value="null">
                                    Select category
                                </option>

                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>

                            <select v-else v-model="row.financial_account_id"
                                class="rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200">
                                <option :value="null">
                                    Select cash account
                                </option>

                                <option v-for="account in accounts" :key="account.id" :value="account.id">
                                    {{ account.account_name }}
                                </option>
                            </select>
                        </div>

                        <input v-model="row.amount" type="number" step="0.01" min="0" placeholder="Amount"
                            class="rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />

                        <input v-model="row.description" type="text" placeholder="Description"
                            class="rounded-md border border-gray-300 bg-gray-50 px-3 py-2 font-medium shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-200" />

                        <button type="button" class="px-3 text-sm font-medium text-gray-600 hover:text-red-600"
                            @click="removeSplitRow(index)">
                            Remove
                        </button>
                    </div>

                    <button type="button" class="mb-5 text-sm font-medium text-[#477b67] hover:underline"
                        @click="addSplitRow">
                        + Add another split
                    </button>

                    <div class="space-y-1 border-t border-gray-300 pt-4 text-sm">
                        <div class="flex justify-between">
                            <span>Transaction</span>

                            <span>
                                {{ transactionAmount.toFixed(2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span>Assigned</span>

                            <span>
                                {{ splitTotal.toFixed(2) }}
                            </span>
                        </div>

                        <div class="flex justify-between font-semibold">
                            <span>Remaining</span>

                            <span>
                                {{ splitRemaining.toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <button type="button" :disabled="!splitBalanced"
                        class="mt-5 rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white shadow-sm hover:bg-[#3d6b59] disabled:opacity-40"
                        @click="saveSplit">
                        Save Split
                    </button>
                </div>
            </div>
        </div>

        <!--  NO CURRENT TRANSACTION   -->
        <div v-else class="rounded-2xl border border-gray-300 bg-white p-10 text-center shadow-sm">
            <template v-if="remaining > 0">
                <div class="text-xl font-semibold">
                    {{ remaining }} transactions still need attention
                </div>

                <p class="mt-2 text-sm text-muted-foreground">
                    These transactions were marked Do Later.
                </p>

                <Link :href="`/households/${household.id}/transactions/assign?deferred=1`"
                    class="mt-5 inline-flex rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white shadow-sm hover:bg-[#3d6b59]">
                    Work on Deferred Transactions
                </Link>
            </template>
        </div>
    </div>
</template>
