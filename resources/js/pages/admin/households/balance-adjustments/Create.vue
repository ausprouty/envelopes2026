<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Category = {
    current_balance: number;
    dashboard_image: string | null;
    id: number;
    name: string;
};

type Household = {
    default_currency: string;
    household_name: string;
    id: number;
};

type BalanceRow = {
    balance: string;
    category_id: number;
};

const props = defineProps<{
    categories: Category[];
    household: Household;
}>();

const adjustmentDate = ref('2026-09-01');

const reason = ref('September 2026 envelope blance reset');

const balances = ref<BalanceRow[]>(
    props.categories.map(category => ({
        balance: '0.00',
        category_id: category.id,
    })),
);

const assignedTotal = computed(() => {
    return balances.value.reduce(
        (total, row) =>
            total + (Number(row.balance) || 0),
        0,
    );
});

const money = (amount: number) => {
    return Number(amount ?? 0).toLocaleString(
        'en-US',
        {
            currency:
                props.household.default_currency ?? 'USD',
            style: 'currency',
        },
    );
};

function currentBalance(categoryId: number) {
    return (
        props.categories.find(
            category => category.id === categoryId,
        )?.current_balance ?? 0
    );
}

function categoryName(categoryId: number) {
    return (
        props.categories.find(
            category => category.id === categoryId,
        )?.name ?? ''
    );
}

function submit() {
    router.post(
        `/admin/households/${props.household.id}/balance-adjustments`,
        {
            adjustment_date: adjustmentDate.value,
            balances: balances.value,
            reason: reason.value,
        },
    );
}
</script>

<template>

    <Head title="Start New Budget" />

    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">
                Reset Envelope Balances
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                {{ household.household_name }}
            </p>
        </div>

        <div class="rounded-2xl border border-gray-300 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Starting date
                    </label>

                    <input v-model="adjustmentDate" type="date"
                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">
                        Reason
                    </label>

                    <input v-model="reason" type="text"
                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2" />
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-300 bg-white shadow-sm">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            Envelope
                        </th>

                        <th class="px-4 py-3 text-right">
                            Current
                        </th>

                        <th class="px-4 py-3 text-right">
                            New Balance
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in balances" :key="row.category_id" class="border-t border-gray-300">
                        <td class="px-4 py-3 font-medium">
                            {{ categoryName(row.category_id) }}
                        </td>

                        <td class="px-4 py-3 text-right text-gray-600">
                            {{
                                money(
                                    currentBalance(
                                        row.category_id,
                                    ),
                                )
                            }}
                        </td>

                        <td class="px-4 py-3">
                            <input v-model="row.balance" type="number" step="0.01"
                                class="ml-auto block w-36 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-right" />
                        </td>
                    </tr>
                </tbody>

                <tfoot class="border-t-2 border-gray-400 bg-gray-50">
                    <tr>
                        <td class="px-4 py-4 font-semibold">
                            Total Assigned
                        </td>

                        <td></td>

                        <td class="px-4 py-4 text-right text-lg font-semibold">
                            {{ money(assignedTotal) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex justify-end">
            <button type="button"
                class="rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white shadow-sm hover:bg-[#3d6b59]"
                @click="submit">
                Reset Envelope Balances
            </button>
        </div>
    </div>
</template>
