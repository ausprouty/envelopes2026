<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from '@/lib/route';

interface BalanceHistory {
    balance_date: string;
    id: number;
    ledger_balance: string | number | null;
}

interface FinancialAccount {
    account_name: string;
    account_type: string;
    category_type: string;
    currency: string;
    id: number;
    institution_name: string | null;
    latest_balance_history: BalanceHistory | null;
}

interface Household {
    household_name: string;
    id: number;
}

defineProps<{
    accounts: FinancialAccount[];
    household: Household;
}>();

function formatBalance(
    balance: string | number | null,
    currency: string,
): string {
    if (balance === null) {
        return 'No balance recorded';
    }

    return new Intl.NumberFormat('en-AU', {
        currency,
        style: 'currency',
    }).format(Number(balance));
}

function formatDate(date: string | null): string {
    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${date}T00:00:00`));
}

function formatAccountType(accountType: string): string {
    return accountType
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}
</script>

<template>

    <Head title="Account Balances" />

    <div class="mx-auto max-w-6xl p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    Account Balances
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    {{ household.household_name }}
                </p>
            </div>

            <Link :href="route('households.balances.create', {
                household: household.id,
            })
                " class="rounded bg-blue-600 px-4 py-2 text-white">
                Record Balance
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg border bg-white">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">
                            Account
                        </th>

                        <th class="px-4 py-3 text-left text-sm font-semibold">
                            Type
                        </th>

                        <th class="px-4 py-3 text-right text-sm font-semibold">
                            Last Balance
                        </th>

                        <th class="px-4 py-3 text-left text-sm font-semibold">
                            Balance Date
                        </th>

                        <th class="px-4 py-3 text-right text-sm font-semibold">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="account in accounts" :key="account.id" class="border-t">
                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ account.account_name }}
                            </div>

                            <div v-if="account.institution_name" class="text-sm text-gray-500">
                                {{ account.institution_name }}
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            {{
                                formatAccountType(
                                    account.account_type,
                                )
                            }}
                        </td>

                        <td class="px-4 py-3 text-right" :class="{
                            'text-gray-500':
                                !account.latest_balance_history,
                        }">
                            {{
                                formatBalance(
                                    account.latest_balance_history
                                        ?.ledger_balance ?? null,
                                    account.currency,
                                )
                            }}
                        </td>

                        <td class="px-4 py-3">
                            {{
                                formatDate(
                                    account.latest_balance_history
                                        ?.balance_date ?? null,
                                )
                            }}
                        </td>

                        <td class="space-x-4 px-4 py-3 text-right">
                            <Link :href="route('households.balances.create', {
                                household: household.id,
                                financial_account_id: account.id,
                            })
                                " class="text-blue-600 hover:underline">
                                Record
                            </Link>

                            <span class="text-gray-400">
                                History
                            </span>
                        </td>
                    </tr>

                    <tr v-if="accounts.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            No active financial accounts found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
