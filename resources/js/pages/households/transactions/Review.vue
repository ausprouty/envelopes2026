<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Check, Tag } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps<{
    household: {
        id: number;
        household_name: string;
    };

    transaction: {
        id: number;
        transaction_date: string;
        payee: string | null;
        amount: number | string;
        currency: string;
    } | null;

    categories: Array<{
        id: number;
        name: string;
    }>;

    remaining: number;
}>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
const categoryId = ref<number | ''>('');
const always = ref(false);
const matchText = ref('');
const saving = ref(false);

const amount = computed(() => {
    if (!props.transaction) {
        return '';
    }

    return Number(props.transaction.amount).toFixed(2);
});

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
        item => upperPayee.includes(item)
    );

    return match ?? payee;
}

function toggleAlways() {
    if (always.value && !matchText.value) {
        matchText.value = suggestMatchText();
    }
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
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                categoryId.value = '';
                always.value = false;
                matchText.value = '';
            },

            onFinish: () => {
                saving.value = false;
            },
        }
    );
}
</script>

<template>

    <Head title="Review Transactions" />

    <div class="mx-auto max-w-3xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#477b67] text-white">
                    <Tag class="h-6 w-6" />
                </div>

                <div>
                    <h1 class="text-2xl font-semibold">
                        Review Transactions
                    </h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ household.household_name }}
                    </p>
                </div>
            </div>

            <div class="rounded-full bg-muted px-3 py-1 text-sm text-muted-foreground">
                {{ remaining }} remaining
            </div>
        </div>

        <div v-if="transaction" class="rounded-2xl border bg-white p-6 shadow-sm">
            <div class="border-b pb-5">
                <div class="text-sm text-gray-500">
                    {{ formatDate(transaction.transaction_date) }}
                </div>

                <div class="mt-2 text-xl font-semibold text-gray-900">
                    {{ transaction.payee || 'Unknown payee' }}
                </div>

                <div class="mt-3 text-3xl font-semibold" :class="Number(transaction.amount) < 0
                    ? 'text-red-600'
                    : 'text-emerald-700'
                    ">
                    {{ transaction.currency }}
                    {{ amount }}
                </div>
            </div>

            <div class="space-y-5 pt-5">
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Category
                    </label>

                    <select v-model="categoryId" class="w-full rounded-md border bg-background px-3 py-2">
                        <option value="">
                            Select a category
                        </option>

                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>

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

                    <input v-model="matchText" type="text" class="w-full rounded-md border bg-background px-3 py-2" />

                    <p class="mt-1 text-sm text-muted-foreground">
                        You can shorten this to the stable part of the payee,
                        such as SAFEWAY or NETFLIX.
                    </p>
                </div>

                <button type="button" :disabled="!categoryId || saving"
                    class="inline-flex items-center gap-2 rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white disabled:opacity-50"
                    @click="saveAndNext">
                    <Check class="h-4 w-4" />

                    {{
                        saving
                            ? 'Saving...'
                            : 'Save & Next'
                    }}
                </button>
            </div>
        </div>

        <div v-else class="rounded-2xl border bg-white p-10 text-center">
            <div class="text-xl font-semibold">
                All caught up
            </div>

            <p class="mt-2 text-sm text-muted-foreground">
                There are no uncategorized transactions left.
            </p>
        </div>
    </div>
</template>
