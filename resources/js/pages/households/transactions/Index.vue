<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Upload, ClipboardCheck } from '@lucide/vue';


defineProps<{
    household: {
        id: number;
        household_name: string;
    };

    transactions: Array<{
        id: number;
        transaction_date: string;
        payee: string | null;
        amount: number | string;
        currency: string;

        category: {
            name: string;
        } | null;

        financial_account: {
            account_name: string;
        } | null;
    }>;
}>();



function formatDate(date: string | null) {
    if (!date) {
        return '—';
    }

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(new Date(date));
}
</script>

<template>
    <div class="p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Transactions
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    {{ household.household_name }}
                </p>
            </div>

            <div class="flex items-center gap-3">


                <Link :href="`/households/${household.id}/transactions/import`"
                    class="inline-flex items-center gap-2 rounded-md bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    <Upload class="h-4 w-4" />
                    Import
                </Link>
                <Link :href="`/households/${household.id}/transactions/review`"
                    class="inline-flex items-center gap-2 rounded-md bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    <ClipboardCheck class="h-4 w-4" />
                    Review
                </Link>
                <Link :href="`/households/${household.id}/income-allocations/create`"
                    class="inline-flex items-center gap-2 rounded-md bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    Allocate Income
                </Link>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                            Date
                        </th>

                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                            Payee
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">
                            Amount
                        </th>

                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                            Category
                        </th>



                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                            Account
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="transaction in transactions" :key="transaction.id">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                            {{ formatDate(transaction.transaction_date) }}
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ transaction.payee || '—' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-gray-900">
                            {{ transaction.currency }}
                            {{ Number(transaction.amount).toFixed(2) }}
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ transaction.category?.name || '—' }}
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ transaction.financial_account?.account_name || '—' }}
                        </td>
                    </tr>

                    <tr v-if="transactions.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                            No transactions found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
