<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    household: {
        id: number;
        household_name: string;
        default_currency: string;
    };
    categories: {
        id: number;
        name: string;
        current_balance: number;
        context: string;
    }[];
}>();



const fromCategoryId = ref<number | null>(null);
const toCategoryId = ref<number | null>(null);
const amount = ref('');
const transferDate = ref(new Date().toISOString().slice(0, 10));
const description = ref('');

const page = usePage();

const errors = computed(() => page.props.errors as Record<string, string>);
const success = computed(() => {
    return (page.props.flash as { success?: string } | undefined)?.success;
});
const context = ref<'household' | 'ministry_au'>('household');

watch(context, () => {
    fromCategoryId.value = null;
    toCategoryId.value = null;
});
watch(fromCategoryId, () => {
    if (toCategoryId.value === fromCategoryId.value) {
        toCategoryId.value = null;
    }
});

const filteredCategories = computed(() =>
    props.categories.filter(
        category => category.context === context.value
    )
);
const destinationCategories = computed(() =>
    filteredCategories.value.filter(
        category => category.id !== fromCategoryId.value
    )
);

const fromCategory = computed(() =>
    props.categories.find(
        category => category.id === fromCategoryId.value
    )
);

const toCategory = computed(() =>
    props.categories.find(
        category => category.id === toCategoryId.value
    )
);

const transferAmount = computed(() => {
    const value = Number(amount.value);

    return Number.isFinite(value) && value > 0
        ? value
        : 0;
});

const fromBalanceAfter = computed(() => {
    if (!fromCategory.value) {
        return null;
    }

    return fromCategory.value.current_balance - transferAmount.value;
});

const toBalanceAfter = computed(() => {
    if (!toCategory.value) {
        return null;
    }

    return toCategory.value.current_balance + transferAmount.value;
});

const submit = () => {
    router.post(
        `/households/${props.household.id}/category-transfers`,
        {
            from_category_id: fromCategoryId.value,
            to_category_id: toCategoryId.value,
            amount: amount.value,
            transfer_date: transferDate.value,
            description: description.value,
        }
    );
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.household.default_currency,
    }).format(value);
};
</script>

<template>

    <Head title="Move Between Envelopes" />

    <div class="p-4 sm:p-6">
        <div class="mb-6">
            <Link :href="`/households/${household.id}/dashboard`"
                class="text-sm font-medium text-[#477b67] hover:underline">
                Dashboard
            </Link>
        </div>

        <div class="mx-auto max-w-2xl">
            <h1 class="mb-6 text-2xl font-semibold">
                Transfer Money
            </h1>
            <div v-if="success"
                class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ success }}
            </div>

            <div class="mb-6 flex gap-2">
                <button type="button" class="rounded-md px-4 py-2 text-sm font-medium" :class="context === 'household'
                    ? 'bg-[#477b67] text-white'
                    : 'border bg-white text-gray-700'
                    " @click="context = 'household'">
                    Personal
                </button>

                <button type="button" class="rounded-md px-4 py-2 text-sm font-medium" :class="context === 'ministry_au'
                    ? 'bg-[#477b67] text-white'
                    : 'border bg-white text-gray-700'
                    " @click="context = 'ministry_au'">
                    Ministry
                </button>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        From
                    </label>

                    <select v-model="fromCategoryId" class="w-full rounded-md border px-3 py-2">
                        <option :value="null">
                            Select an envelope
                        </option>

                        <option v-for="category in filteredCategories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>

                    <div v-if="fromCategory" class="mt-2 text-sm text-gray-600">
                        Available:
                        {{ formatCurrency(fromCategory.current_balance) }}
                    </div>
                </div>

                <p v-if="errors.from_category_id" class="mt-1 text-sm text-red-600">
                    {{ errors.from_category_id }}
                </p>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        To
                    </label>

                    <select v-model="toCategoryId" class="w-full rounded-md border px-3 py-2">
                        <option :value="null">
                            Select an envelope
                        </option>

                        <option v-for="category in destinationCategories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>

                    <div v-if="toCategory" class="mt-2 text-sm text-gray-600">
                        Current balance:
                        {{ formatCurrency(toCategory.current_balance) }}
                    </div>
                </div>
                <p v-if="errors.to_category_id" class="mt-1 text-sm text-red-600">
                    {{ errors.to_category_id }}
                </p>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Amount
                    </label>

                    <input v-model="amount" type="number" step="0.01" min="0.01"
                        class="w-full rounded-md border px-3 py-2" />
                </div>
                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">
                    {{ errors.amount }}
                </p>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Date
                    </label>

                    <input v-model="transferDate" type="date" class="w-full rounded-md border px-3 py-2" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Note
                    </label>

                    <input v-model="description" type="text" class="w-full rounded-md border px-3 py-2" />
                </div>
                <div v-if="fromCategory && toCategory && transferAmount > 0" class="rounded-lg border bg-gray-50 p-4">
                    <div class="mb-3 font-medium">
                        After this move
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <span>{{ fromCategory.name }}</span>

                            <span>
                                {{ formatCurrency(fromCategory.current_balance) }}
                                →
                                <strong>
                                    {{ formatCurrency(fromBalanceAfter ?? 0) }}
                                </strong>
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span>{{ toCategory.name }}</span>

                            <span>
                                {{ formatCurrency(toCategory.current_balance) }}
                                →
                                <strong>
                                    {{ formatCurrency(toBalanceAfter ?? 0) }}
                                </strong>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="!fromCategory ||
                        !toCategory ||
                        transferAmount <= 0 ||
                        transferAmount > fromCategory.current_balance
                        "
                        class="rounded-md bg-[#477b67] px-4 py-2 font-medium text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                        Move Money
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
