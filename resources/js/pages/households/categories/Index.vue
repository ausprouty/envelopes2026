<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

type Household = {
    id: number;
    household_name: string;
};

type Category = {
    id: number;
    code: string | null;
    name: string;
    parent_category_id: number | null;
    category_type: string;
    context: string;
    tracks_balance: boolean;
    is_active: boolean;
    display_order: number;
};

const props = defineProps<{
    household: Household;
    categories: Category[];
}>();

const typeLabel = (type: string) => {
    const labels: Record<string, string> = {
        income: 'Income',
        expense: 'Expense',
        asset: 'Asset',
        transfer: 'Transfer',
        reimbursement: 'Reimbursement',
        heading: 'Heading',
    };

    return labels[type] ?? type;
};

const contextLabel = (context: string) => {
    const labels: Record<string, string> = {
        household: 'Household',
        ministry_au: 'Ministry AU',
        ministry_us: 'Ministry US',
        other: 'Other',
    };

    return labels[context] ?? context;
};
</script>

<template>

    <Head title="Categories" />

    <div class="mx-auto max-w-7xl p-6">
        <!-- Heading -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    Categories
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ household.household_name }}
                </p>
            </div>

            <Link :href="`/households/${household.id}/categories/create`"
                class="inline-flex items-center justify-center rounded-lg bg-[#477b67] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#3c6958]">
                Add Category
            </Link>
        </div>

        <!-- Empty state -->
        <div v-if="categories.length === 0"
            class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                No categories yet
            </h2>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Add your first category to begin organizing transactions.
            </p>

            <Link :href="`/households/${household.id}/categories/create`"
                class="mt-5 inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">
                Add First Category
            </Link>
        </div>

        <!-- Categories table -->
        <div v-else
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Order
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Code
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Category
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Type
                            </th>

                            <th
                                class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Balance
                            </th>

                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Context
                            </th>



                            <th
                                class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Status
                            </th>

                            <th class="px-4 py-3">
                                <span class="sr-only">Edit</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="category in categories" :key="category.id" :class="[
                            'transition hover:bg-gray-50 dark:hover:bg-gray-800/60',
                            !category.is_active
                                ? 'opacity-50'
                                : '',
                        ]">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ category.display_order }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ category.code || '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <div :class="[
                                    'text-gray-900 dark:text-white',
                                    category.category_type === 'heading'
                                        ? 'text-base font-bold text-[#477b67] dark:text-[#8fc0aa]'
                                        : 'text-sm font-medium',
                                    category.parent_category_id
                                        ? 'pl-6'
                                        : '',
                                ]">
                                    <span v-if="category.parent_category_id" class="mr-2 text-gray-300">
                                        ↳
                                    </span>

                                    {{ category.name }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3">
                                <span
                                    class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ typeLabel(category.category_type) }}
                                </span>
                            </td>
                            <td
                                class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-300">
                                {{ category.tracks_balance ? 'Yes' : 'No' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ contextLabel(category.context) }}
                            </td>



                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <span v-if="category.is_active"
                                    class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                    Active
                                </span>

                                <span v-else
                                    class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                    Inactive
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <Link :href="`/households/${household.id}/categories/${category.id}/edit`"
                                    class="font-medium text-gray-700 hover:text-gray-950 dark:text-gray-300 dark:hover:text-white">
                                    Edit
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
