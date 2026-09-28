<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps<{
    household: {
        id: number;
        household_name: string;
    };
}>();
</script>

<template>

    <Head title="Reports" />

    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-4xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Reports
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Choose a report.
                </p>
            </div>

            <div class="grid gap-4">
                <Link :href="route('households.reports.spending-by-category', { household: props.household.id })"
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-[#477b67] hover:shadow-md">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-[#477b67]">
                                Spending by Category
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Compare actual spending with your usual monthly allocation.
                            </p>
                        </div>

                        <span class="text-xl text-[#477b67]">
                            →
                        </span>
                    </div>
                </Link>

                <Link :href="route('households.reports.category-balances',
                { household: props.household.id })"
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-[#477b67] hover:shadow-md">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-[#477b67]">
                                Category Balances
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                See how much is currently available in each envelope.
                            </p>
                        </div>

                        <span class="text-xl text-[#477b67]">
                            →
                        </span>
                    </div>
                </Link>

                <Link :href="route('households.reports.income-expenses', { household: props.household.id })"
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-[#477b67] hover:shadow-md">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-[#477b67]">
                                Income and Expenses
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Review income and spending over time.
                            </p>
                        </div>

                        <span class="text-xl text-[#477b67]">
                            →
                        </span>
                    </div>
                </Link>

                <Link :href="route('households.reports.account-balances', { household: props.household.id })"
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-[#477b67] hover:shadow-md">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-[#477b67]">
                                Account Balances
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                View balances across your financial accounts.
                            </p>
                        </div>

                        <span class="text-xl text-[#477b67]">
                            →
                        </span>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
