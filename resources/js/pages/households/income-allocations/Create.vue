<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { categoryColors } from '@/lib/categoryColors';

type Household = {
    id: number;
    household_name: string;
    default_currency: string;
};

type Category = {
    id: number;
    name: string;
    is_heading: boolean;
    dashboard_image: string | null;
    current_balance: number | string;
    normal_amount: number | string | null;
};

type AllocationLine = {
    category_id: number;
    amount: number;
};

type CategoryWithColor = Category & {
    colorIndex: number;
};

const props = defineProps<{
    household: Household;
    categories: Category[];
    availableToAllocate: number | string;
}>();

const form = useForm<{
    allocation_date: string;
    amount: number;
    notes: string;
    lines: AllocationLine[];
}>({
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

const categoriesWithColors = computed<CategoryWithColor[]>(() => {
    let headingIndex = -1;

    return props.categories.map((category) => {
        if (category.is_heading) {
            headingIndex++;
        }

        return {
            ...category,
            colorIndex: Math.max(headingIndex, 0),
        };
    });
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
        Number(props.availableToAllocate) -
        allocatedTotal.value,
);

const canSave = computed(
    () =>
        allocatedTotal.value > 0 &&
        allocatedTotal.value <= Number(props.availableToAllocate),
);

const lineForCategory = (categoryId: number) =>
    form.lines.find(
        (line) => line.category_id === categoryId,
    );

const projectedBalance = (category: Category) => {
    const line = lineForCategory(category.id);

    return (
        Number(category.current_balance || 0) +
        Number(line?.amount || 0)
    );
};

const money = (value: number | string | null) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.household.default_currency || 'USD',
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
    <div class="mx-auto max-w-6xl space-y-6 p-4 sm:p-6">
        <!-- Heading -->
        <!-- Heading -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-slate-950">
                    Allocate Income
                </h1>

                <p class="mt-1 text-base text-slate-500">
                    Decide where the money in the Income Pool should go.
                </p>
            </div>

            <button type="button"
                class="rounded-2xl bg-[#477b67] px-6 py-3 font-semibold text-white shadow-sm transition hover:opacity-90"
                @click="loadNormal">
                Load Normal
            </button>
        </div>

        <!-- Summary -->
        <div class="grid gap-5 rounded-3xl bg-[#477b67] p-6 text-white shadow-sm sm:grid-cols-3">
            <div>
                <div class="text-sm text-white/75">
                    Available
                </div>

                <div class="mt-1 text-3xl font-semibold">
                    {{ money(availableToAllocate) }}
                </div>
            </div>

            <div>
                <div class="text-sm text-white/75">
                    Assigned
                </div>

                <div class="mt-1 text-3xl font-semibold">
                    {{ money(allocatedTotal) }}
                </div>
            </div>

            <div>
                <div class="text-sm text-white/75">
                    Still to assign
                </div>

                <div class="mt-1 text-3xl font-semibold" :class="{
                    'text-white': remaining > 0.005,
                    'text-green-200': Math.abs(remaining) < 0.005,
                    'text-red-200': remaining < 0,
                }">
                    {{ money(remaining) }}
                </div>
            </div>
        </div>



        <form class="space-y-6" @submit.prevent="submit">
            <!-- Allocation Grid -->
            <div class="overflow-hidden rounded-3xl border border-gray-400 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <!-- Green table header -->
                        <thead class="bg-[#477b67] text-white">
                            <tr>
                                <th class="border border-gray-600 px-5 py-4 text-left font-semibold text-white">
                                    Category
                                </th>

                                <th class="border border-gray-600 px-5 py-4 text-right font-semibold text-white">
                                    Current Balance
                                </th>

                                <th class="border border-gray-600 px-5 py-4 text-right font-semibold text-white">
                                    Normal
                                </th>

                                <th class="border border-gray-600 px-5 py-4 text-right font-semibold text-white">
                                    This Allocation
                                </th>

                                <th class="border border-gray-600 px-5 py-4 text-right font-semibold text-white">
                                    New Balance
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <template v-for="category in categoriesWithColors" :key="category.id">
                                <!-- Category Heading -->
                                <tr v-if="category.is_heading">
                                    <td colspan="5" :class="[
                                        'border-b border-gray-300 px-5 py-4 text-lg font-bold text-slate-900',
                                        categoryColors[
                                            category.colorIndex % categoryColors.length
                                        ].heading,
                                    ]">
                                        {{ category.name }}
                                    </td>
                                </tr>

                                <!-- Envelope -->
                                <tr v-else :class="categoryColors[
                                    category.colorIndex % categoryColors.length
                                ].child
                                    ">
                                    <!-- Category -->
                                    <td class="border-b border-r border-gray-300 px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <img v-if="category.dashboard_image"
                                                :src="`/images/categories/${category.dashboard_image}`"
                                                :alt="category.name" class="h-10 w-10 shrink-0 object-contain" />

                                            <span class="font-medium text-slate-900">
                                                {{ category.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Current Balance -->
                                    <td class="border-b border-r border-gray-300 px-5 py-4 text-right" :class="Number(category.current_balance) < 0
                                        ? 'font-semibold text-red-600'
                                        : 'text-slate-700'
                                        ">
                                        {{ money(category.current_balance) }}
                                    </td>

                                    <!-- Normal -->
                                    <td class="border-b border-r border-gray-300 px-5 py-4 text-right text-slate-600">
                                        {{ money(category.normal_amount) }}
                                    </td>

                                    <!-- This Allocation -->
                                    <td class="border-b border-r border-gray-300 px-5 py-4 text-right">
                                        <input v-if="lineForCategory(category.id)" v-model.number="lineForCategory(category.id)!.amount
                                            " type="number" min="0" step="0.01"
                                            class="money-input w-32 rounded-lg border border-gray-400 bg-white px-3 py-2 text-right text-base outline-none focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/15" />
                                    </td>

                                    <!-- New Balance -->
                                    <td class="border-b border-gray-300 px-5 py-4 text-right font-semibold" :class="projectedBalance(category) < 0
                                        ? 'text-red-600'
                                        : 'text-[#477b67]'
                                        ">
                                        {{ money(projectedBalance(category)) }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button"
                    class="rounded-2xl bg-[#477b67] px-5 py-3 font-semibold text-white shadow-sm transition hover:opacity-90"
                    @click="saveNormal">
                    Save as Normal Allocation
                </button>

                <button type="submit" :disabled="!canSave || form.processing"
                    class="rounded-2xl bg-[#477b67] px-6 py-3 font-semibold text-white shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">
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
