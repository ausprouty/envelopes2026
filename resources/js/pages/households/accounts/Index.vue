<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Household {
    id: number;
    household_name: string;
    default_currency: string;
}

interface Account {
    id: number;
    account_name: string;
    institution_name: string | null;
    account_type: string;
    currency: string;
    account_reference: string | null;
    warning_balance: number | null;
    is_active: boolean;
}

defineProps<{
    household: Household;
    accounts: Account[];
}>();

function formatAccountType(type: string): string {
    return type
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Financial Accounts
                    </h1>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ household.household_name }}
                    </p>
                </div>

                <Link :href="`/households/${household.id}/accounts/create`"
                    class="inline-flex items-center justify-center rounded-lg bg-[#477b67] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3d6b59]">
                    + Add Account
                </Link>
            </div>

            <div v-if="accounts.length === 0"
                class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    No accounts yet
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Add the first financial account for this household.
                </p>
            </div>

            <div v-else class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="hidden md:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Account
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Institution
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Type
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Currency
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600">
                                    Status
                                </th>

                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-600">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="account in accounts" :key="account.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ account.account_name }}
                                    </div>

                                    <div v-if="account.account_reference" class="mt-1 text-xs text-gray-500">
                                        {{ account.account_reference }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ account.institution_name || '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ formatAccountType(account.account_type) }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ account.currency }}
                                </td>

                                <td class="px-6 py-4">
                                    <span v-if="account.is_active"
                                        class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">
                                        Active
                                    </span>

                                    <span v-else
                                        class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                        Inactive
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <Link :href="`/households/${household.id}/accounts/${account.id}/edit`"
                                        class="text-sm font-medium text-[#477b67] hover:underline">
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
                <div class="space-y-3 md:hidden">
                    <div v-for="account in accounts" :key="account.id"
                        class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-lg font-semibold text-gray-900">
                                    {{ account.account_name }}
                                </div>

                                <div class="mt-1 text-sm text-gray-500">
                                    {{ account.institution_name || 'No institution' }}
                                </div>
                            </div>

                            <div class="text-sm font-semibold text-[#477b67]">
                                {{ account.currency }}
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                            <span class="text-sm text-gray-500">
                                {{ account.account_type }}
                            </span>

                            <Link :href="`/households/${household.id}/accounts/${account.id}/edit`"
                                class="text-sm font-medium text-[#477b67] hover:underline">
                                Edit
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
