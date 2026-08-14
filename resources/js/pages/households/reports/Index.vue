<script setup lang="ts">

import { Link } from '@inertiajs/vue3';

defineProps<{
    household: {
        id: number;
        household_name: string;
    };
}>();

</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-semibold">
            Reports
        </h1>

        <p class="mt-2 text-muted-foreground">
            Choose a report.
        </p>

        <div class="mt-6 space-y-3">
            <Link :href="`/households/${household.id}/reports/spending-by-category`"
                class="block rounded-xl border border-gray-200 bg-white px-5 py-5 text-lg font-medium text-gray-900 shadow-sm transition hover:bg-gray-50">
                Spending by Category
            </Link>

            <Link :href="`/households/${household.id}/reports/category-balances`"
                class="block rounded-xl border border-gray-200 bg-white px-5 py-5 text-lg font-medium text-gray-900 shadow-sm transition hover:bg-gray-50">
                Category Balances
            </Link>

            <div class="rounded-xl border p-4">
                Income and Expenses
            </div>

            <div class="rounded-xl border p-4">
                Account Balances
            </div>
        </div>
    </div>
</template>
