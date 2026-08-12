<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CircleDollarSign,
    ReceiptText,
} from '@lucide/vue';

interface Household {
    id: number;
    household_name: string;
    default_currency: string;
}

interface Envelope {
    id: number;
    name: string;
    image: string | null;
    current_balance: number;
    spent_this_month: number;
}

interface Transaction {
    id: number;
    transaction_date: string;
    payee: string | null;
    description: string | null;
    amount: number | string;
    currency: string;
}

const props = defineProps<{
    household: Household;
    envelope: Envelope;
    transactions: Transaction[];
}>();

const money = (amount: number | string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.household.default_currency || 'USD',
    }).format(Number(amount));
};

const shortDate = (date: string) => {
    const dateOnly = date.substring(0, 10);

    const [year, month, day] = dateOnly.split('-').map(Number);

    return new Date(year, month - 1, day).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>

    <Head :title="envelope.name" />

    <div class="p-4 sm:p-6">
        <!-- Back -->
        <div class="mb-5">
            <Link :href="`/households/${household.id}/dashboard`"
                class="inline-flex items-center gap-2 text-sm font-medium text-[#477b67] hover:underline">
                <ArrowLeft class="h-4 w-4" />
                Dashboard
            </Link>
        </div>

        <!-- Envelope heading -->
        <div class="flex items-center gap-4">

            <div v-if="envelope.image" class="flex h-20 w-20 shrink-0 items-center justify-center">
                <img :src="`/images/categories/${envelope.image}`" :alt="envelope.name"
                    class="h-full w-full object-contain" />
            </div>
            <div>
                <h1 class="text-3xl font-semibold text-slate-950">
                    {{ envelope.name }}
                </h1>

                <div class="mt-1 text-lg text-slate-500">
                    {{ household.household_name }}
                </div>

            </div>
        </div>

        <!-- Summary -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <!-- Current Balance -->
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67]/10 text-[#477b67]">
                        <CircleDollarSign class="h-7 w-7" />
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-500">
                            Current Balance
                        </div>

                        <div class="mt-1 text-3xl font-semibold" :class="Number(envelope.current_balance) < 0
                            ? 'text-red-600'
                            : 'text-gray-900'
                            ">
                            {{ money(envelope.current_balance) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spent This Month -->
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67]/10 text-[#477b67]">
                        <ReceiptText class="h-7 w-7" />
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-500">
                            Spent This Month
                        </div>

                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ money(envelope.spent_this_month) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold text-gray-900">
                    Recent Transactions
                </h2>

                <div class="mt-1 text-sm text-gray-500">
                    Last 20 transactions
                </div>
            </div>

            <!-- No transactions -->
            <div v-if="transactions.length === 0" class="px-6 py-12 text-center">
                <ReceiptText class="mx-auto mb-3 h-8 w-8 text-[#477b67]" />

                <div class="font-medium text-gray-900">
                    No transactions yet
                </div>

                <div class="mt-1 text-sm text-gray-500">
                    Transactions assigned to this envelope will appear here.
                </div>
            </div>

            <!-- Transactions -->
            <div v-else class="divide-y divide-gray-100">
                <div v-for="transaction in transactions" :key="transaction.id"
                    class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <div class="min-w-0">
                        <div class="truncate font-medium text-gray-900">
                            {{
                                transaction.payee ||
                                transaction.description ||
                                'Transaction'
                            }}
                        </div>

                        <div class="mt-1 text-sm text-gray-500">
                            {{ shortDate(transaction.transaction_date) }}
                        </div>

                        <div v-if="
                            transaction.payee &&
                            transaction.description &&
                            transaction.description !== transaction.payee
                        " class="mt-1 truncate text-xs text-gray-400">
                            {{ transaction.description }}
                        </div>
                    </div>

                    <div class="shrink-0 text-right font-semibold" :class="Number(transaction.amount) < 0
                        ? 'text-gray-900'
                        : 'text-[#477b67]'
                        ">
                        {{ money(transaction.amount) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
