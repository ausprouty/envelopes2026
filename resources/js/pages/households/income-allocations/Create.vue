<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    household: {
        type: Object,
        required: true,
    },

    categories: {
        type: Array,
        required: true,
    },

    availableToAllocate: {
        type: [Number, String],
        required: true,
    },
});

const form = useForm({
    allocation_date: new Date().toISOString().slice(0, 10),
    amount: Number(props.availableToAllocate),
    notes: '',

    lines: props.categories
        .filter((category) => !category.is_heading)
        .map((category) => ({
            category_id: category.id,
            amount: 0,
        })),
});
const loadNormal = () => {
    form.lines.forEach((line) => {
        const category = props.categories.find(
            (category) => category.id === line.category_id,
        );

        line.amount = Number(category?.normal_amount || 0);
    });
};

const allocatedTotal = computed(() =>
    form.lines.reduce(
        (total, line) => total + Number(line.amount || 0),
        0,
    ),
);

const remaining = computed(
    () =>
        Number(props.availableToAllocate)
        - allocatedTotal.value,
);

const canSave = computed(
    () =>
        allocatedTotal.value > 0
        && allocatedTotal.value <= Number(props.availableToAllocate),
);

const lineForCategory = (categoryId) =>
    form.lines.find(
        (line) => line.category_id === categoryId,
    );

const projectedBalance = (category) => {
    const line = lineForCategory(category.id);

    return (
        Number(category.current_balance || 0)
        + Number(line?.amount || 0)
    );
};

const money = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value || 0));

const saveNormal = () => {
    form.post(
        `/households/${props.household.id}/income-allocations/defaults`,
        {
            preserveScroll: true,
        },
    );
};
const submit = () => {
    form.post(
        `/households/${props.household.id}/income-allocations`,
    );
};
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <div>
            <h1 class="text-2xl font-semibold">
                Allocate Income
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Decide where the money in the Income Pool should go.
            </p>
        </div>

        <div class="grid gap-4 rounded-xl border bg-white p-5 shadow-sm sm:grid-cols-3">
            <div>
                <div class="text-sm text-gray-500">
                    Available
                </div>

                <div class="text-2xl font-semibold">
                    {{ money(availableToAllocate) }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Assigned
                </div>

                <div class="text-2xl font-semibold">
                    {{ money(allocatedTotal) }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Still to assign
                </div>

                <div class="text-2xl font-semibold" :class="{
                    'text-green-600':
                        Math.abs(remaining) < 0.005,
                    'text-red-600':
                        remaining < 0,
                }">
                    {{ money(remaining) }}
                </div>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="button" class="rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white hover:opacity-90"
                @click="loadNormal">
                Load Normal
            </button>
        </div>
        <form class="space-y-6" @submit.prevent="submit">
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="w-full">
                    <thead class="border-b bg-gray-50 text-left text-sm">
                        <tr>
                            <th class="px-4 py-3">
                                Category
                            </th>

                            <th class="px-4 py-3 text-right">
                                Current Balance
                            </th>

                            <th class="px-4 py-3 text-right">
                                Normal
                            </th>

                            <th class="px-4 py-3 text-right">
                                This Allocation
                            </th>

                            <th class="px-4 py-3 text-right">
                                New Balance
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-for="category in categories" :key="category.id">
                            <tr v-if="category.is_heading" class="border-b bg-gray-50">
                                <td colspan="5" class="px-4 py-3 text-base font-semibold text-gray-900">
                                    {{ category.name }}
                                </td>
                            </tr>

                            <tr v-else class="border-b last:border-b-0">
                                <td class="px-4 py-3 pl-8 font-medium">
                                    {{ category.name }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ money(category.current_balance) }}
                                </td>

                                <td class="px-4 py-3 text-right text-gray-500">
                                    {{ money(category.normal_amount) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <input v-model.number="lineForCategory(
                                        category.id,
                                    ).amount
                                        " type="number" min="0" step="0.01"
                                        class="money-input w-32 rounded-md border px-3 py-2 text-right" />
                                </td>

                                <td class="px-4 py-3 text-right font-medium">
                                    {{
                                        money(
                                            projectedBalance(
                                                category,
                                            ),
                                        )
                                    }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between">
                <button type="button"
                    class="rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white hover:opacity-90"
                    @click="saveNormal">
                    Save as Normal Allocation
                </button>

                <button type="submit" :disabled="!canSave || form.processing"
                    class="rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">
                    Apply Allocation
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.money-input::-webkit-outer-spin-button,
.money-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.money-input {
    -moz-appearance: textfield;
}
</style>
