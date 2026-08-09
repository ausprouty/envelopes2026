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
    lines: props.categories.map((category) => ({
        category_id: category.id,
        amount: 0,
    })),
});

const allocatedTotal = computed(() =>
    form.lines.reduce(
        (total, line) => total + Number(line.amount || 0),
        0,
    ),
);

const remaining = computed(
    () => Number(props.availableToAllocate) - allocatedTotal.value,
);

const projectedBalance = (category, index) => {
    return (
        Number(category.current_balance || 0) +
        Number(form.lines[index].amount || 0)
    );
};

const canSave = computed(
    () =>
        Number(props.availableToAllocate) > 0 &&
        Math.abs(remaining.value) < 0.005,
);

const money = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value || 0));

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

        <div
            class="grid gap-4 rounded-xl border bg-white p-5 shadow-sm sm:grid-cols-3"
        >
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

                <div
                    class="text-2xl font-semibold"
                    :class="{
                        'text-green-600': Math.abs(remaining) < 0.005,
                        'text-red-600': remaining < 0,
                    }"
                >
                    {{ money(remaining) }}
                </div>
            </div>
        </div>

        <form
            class="space-y-6"
            @submit.prevent="submit"
        >
            <div
                class="overflow-hidden rounded-xl border bg-white shadow-sm"
            >
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
                                This Allocation
                            </th>

                            <th class="px-4 py-3 text-right">
                                New Balance
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(category, index) in categories"
                            :key="category.id"
                            class="border-b last:border-b-0"
                        >
                            <td class="px-4 py-3 font-medium">
                                {{ category.name }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                {{ money(category.current_balance) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <input
                                    v-model.number="form.lines[index].amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-32 rounded-md border px-3 py-2 text-right"
                                />
                            </td>

                            <td class="px-4 py-3 text-right font-medium">
                                {{ money(projectedBalance(category, index)) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="!canSave || form.processing"
                    class="rounded-md bg-[#477b67] px-5 py-2.5 font-medium text-white disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Save Allocation
                </button>
            </div>
        </form>
    </div>
</template>
