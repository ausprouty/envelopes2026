<script setup lang="ts">

import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CircleDollarSign, Eye, Shapes } from '@lucide/vue';
import { computed, ref } from 'vue';
import { categoryColors } from '@/lib/categoryColors';
import { categoryIcons } from '@/lib/categoryIcons';
import type { CategoryIconName } from '@/lib/categoryIcons';
import { formatDate } from '@/lib/formatDate';


type Account = {
    account_name: string;
    available_balance: number | null;
    balance_as_of: string | null;
    currency: string;
    id: number;
    latest_transaction_date: string | null;
    ledger_balance: number | null;
};

type Heading = {
    id: number;
    name: string;
    icon: CategoryIconName | null;
    dashboard_image: string | null;
    balance: number;
};

type WatchCategory = {
    id: number;
    name: string;
    dashboard_image: string | null;
    current_balance: number;
    needs_attention: boolean;
};

const props = defineProps<{
    accounts: Account[];
    headings: Heading[];
    household: {
        default_currency: string;
        household_name: string;
        id: number;
    };
    householdRole: 'admin' | 'member' | 'coach';
    totalAvailable: number;
    watchCategories: WatchCategory[];
}>();

const showAllAccounts = ref(false);

const accountTotals = computed(() => {
    const totals: Record<
        string,
        {
            available: number;
            ledger: number;
        }
    > = {};

    props.accounts.forEach(account => {
        if (!totals[account.currency]) {
            totals[account.currency] = {
                available: 0,
                ledger: 0,
            };
        }

        totals[account.currency].available +=
            Number(account.available_balance ?? 0);

        totals[account.currency].ledger +=
            Number(account.ledger_balance ?? 0);
    });

    return Object.entries(totals).map(
        ([currency, balances]) => ({
            currency,
            ...balances,
        }),
    );
});

function formatAccountBalance(
    amount: number | null,
    currency: string,
): string {
    if (amount === null) {
        return '—';
    }

    return Number(amount).toLocaleString('en-US', {
        style: 'currency',
        currency,
    });
}

</script>

<template>

    <Head title="Dashboard" />

    <div class="p-6">
        <!-- Household context -->
        <div class="mb-4">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ household.household_name }}
            </h1>

            <p v-if="householdRole === 'coach'" class="mt-1 text-sm font-medium text-[#477b67]">
                Coaching Ryan
            </p>
        </div>
        <!-- Encouragement + Available -->
        <div
            class="mb-6 flex flex-col gap-5 rounded-3xl bg-[#477b67] px-6 py-5 text-white shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-xl font-semibold">
                    Give every dollar a purpose.
                </div>

                <div class="mt-1 text-sm text-white/80">
                    Then enjoy the freedom of knowing where it went.
                </div>
            </div>

            <div class="flex items-center gap-3 sm:text-right">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white">
                    <CircleDollarSign class="h-7 w-7" />
                </div>

                <div>
                    <div class="text-sm text-white/75">
                        Available this month
                    </div>

                    <div class="text-3xl font-semibold">
                        {{
                            Number(totalAvailable ?? 0).toLocaleString(
                                'en-US',
                                {
                                    style: 'currency',
                                    currency:
                                        household.default_currency ?? 'USD',
                                },
                            )
                        }}
                    </div>

                    <Link v-if="householdRole !== 'coach'"
                        :href="`/households/${household.id}/income-allocations/create`"
                        class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-white hover:underline">
                        Allocate Income
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Account Balances -->
        <div class="mb-6 overflow-hidden rounded-3xl border border-[#d7e5de] bg-white shadow-lg">
            <!-- Header -->
            <div class="bg-[#eef7f2] px-5 py-5">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67] text-white">
                        <CircleDollarSign class="h-7 w-7" />
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            Account Balances
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            Your bank-reported balances from the latest import
                        </p>
                    </div>
                </div>
            </div>
            <!-- Accounts -->
            <div class="divide-y divide-gray-200">
                <!-- One or two accounts: show normally -->
                <template v-if="accounts.length <= 2">
                    <div v-for="account in accounts" :key="account.id"
                        class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
                        <!-- Account -->
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900">
                                {{ account.account_name }}
                            </div>

                            <div class="mt-1 text-sm text-gray-500">
                                <template v-if="account.balance_as_of">
                                    As of {{ formatDate(account.balance_as_of) }}
                                </template>

                                <template v-else>
                                    No balance imported
                                </template>
                            </div>
                        </div>

                        <!-- Available -->
                        <div class="sm:min-w-32 sm:text-right">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Available
                            </div>

                            <div class="mt-1 text-lg font-bold text-[#477b67]">
                                {{
                                    formatAccountBalance(
                                        account.available_balance,
                                        account.currency,
                                    )
                                }}
                            </div>
                        </div>

                        <!-- Ledger -->
                        <div class="sm:min-w-32 sm:text-right">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Ledger
                            </div>

                            <div class="mt-1 font-semibold text-gray-900">
                                {{
                                    formatAccountBalance(
                                        account.ledger_balance,
                                        account.currency,
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </template>

                <!-- More than two accounts: collapsed summary -->
                <template v-else-if="!showAllAccounts">
                    <div v-for="total in accountTotals" :key="total.currency"
                        class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
                        <div>
                            <div class="font-semibold text-gray-900">
                                Total Account Balance
                            </div>

                            <div class="mt-1 text-sm text-gray-500">
                                {{ accounts.length }} accounts
                                <span v-if="accountTotals.length > 1">
                                    · {{ total.currency }}
                                </span>
                            </div>
                        </div>

                        <div class="sm:min-w-32 sm:text-right">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Available
                            </div>

                            <div class="mt-1 text-lg font-bold text-[#477b67]">
                                {{
                                    formatAccountBalance(
                                        total.available,
                                        total.currency,
                                    )
                                }}
                            </div>
                        </div>

                        <div class="sm:min-w-32 sm:text-right">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Ledger
                            </div>

                            <div class="mt-1 font-semibold text-gray-900">
                                {{
                                    formatAccountBalance(
                                        total.ledger,
                                        total.currency,
                                    )
                                }}
                            </div>
                        </div>
                    </div>

                    <button type="button" class="w-full px-5 py-3 text-sm font-semibold text-[#477b67] hover:bg-gray-50"
                        @click="showAllAccounts = true">
                        Show all {{ accounts.length }} accounts
                    </button>
                </template>

                <!-- Expanded account list -->
                <template v-else>
                    <div v-for="account in accounts" :key="account.id"
                        class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto]">
                        <!-- Account -->
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900">
                                {{ account.account_name }}
                            </div>

                            <div class="mt-1 text-sm text-gray-500">
                                <template v-if="account.balance_as_of">
                                    As of {{ formatDate(account.balance_as_of) }}
                                </template>

                                <template v-else>
                                    No balance imported
                                </template>
                            </div>
                        </div>

                        <!-- Available -->
                        <div class="sm:min-w-32 sm:text-right">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Available
                            </div>

                            <div class="mt-1 text-lg font-bold text-[#477b67]">
                                {{
                                    formatAccountBalance(
                                        account.available_balance,
                                        account.currency,
                                    )
                                }}
                            </div>
                        </div>

                        <!-- Ledger -->
                        <div class="sm:min-w-32 sm:text-right">
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Ledger
                            </div>

                            <div class="mt-1 font-semibold text-gray-900">
                                {{
                                    formatAccountBalance(
                                        account.ledger_balance,
                                        account.currency,
                                    )
                                }}
                            </div>
                        </div>
                    </div>

                    <button type="button" class="w-full px-5 py-3 text-sm font-semibold text-[#477b67] hover:bg-gray-50"
                        @click="showAllAccounts = false">
                        Collapse accounts
                    </button>
                </template>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[340px_minmax(0,1fr)]">
            <!-- Keep an eye on -->
            <div class="overflow-hidden rounded-3xl border border-[#eadfca] bg-[#fffaf0] shadow-lg">
                <!-- Header -->
                <div class="bg-[#f7efe1] p-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67] text-white">
                            <Eye class="h-7 w-7" />
                        </div>

                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                Keep an eye on
                            </h2>

                            <p class="text-sm text-gray-500">
                                Watched and overspent envelopes
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Watched envelopes -->
                <div class="space-y-2 p-3">
                    <template v-for="category in watchCategories" :key="category.id">
                        <Link v-if="householdRole !== 'coach'"
                            :href="`/households/${household.id}/dashboard/envelopes/${category.id}`"
                            class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm">
                            <img v-if="category.dashboard_image" :src="`/images/categories/${category.dashboard_image}`"
                                :alt="category.name" class="h-11 w-11 shrink-0 rounded-full object-cover" />

                            <div class="min-w-0 flex-1">
                                <div class="truncate font-medium text-gray-900">
                                    {{ category.name }}
                                </div>
                            </div>

                            <div class="whitespace-nowrap font-semibold" :class="category.current_balance < 0
                                ? 'text-red-600'
                                : 'text-gray-900'
                                ">
                                {{
                                    Number(
                                        category.current_balance ?? 0,
                                    ).toLocaleString('en-US', {
                                        style: 'currency',
                                        currency:
                                            household.default_currency ??
                                            'USD',
                                    })
                                }}
                            </div>
                        </Link>

                        <div v-else class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm">
                            <img v-if="category.dashboard_image" :src="`/images/categories/${category.dashboard_image}`"
                                :alt="category.name" class="h-11 w-11 shrink-0 rounded-full object-cover" />

                            <div class="min-w-0 flex-1">
                                <div class="truncate font-medium text-gray-900">
                                    {{ category.name }}
                                </div>
                            </div>

                            <div class="whitespace-nowrap font-semibold" :class="category.current_balance < 0
                                ? 'text-red-600'
                                : 'text-gray-900'
                                ">
                                {{
                                    Number(
                                        category.current_balance ?? 0,
                                    ).toLocaleString('en-US', {
                                        style: 'currency',
                                        currency:
                                            household.default_currency ??
                                            'USD',
                                    })
                                }}
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Categories -->
            <div class="overflow-hidden rounded-3xl border border-[#d7e5de] bg-white shadow-lg dark:bg-gray-900">
                <!-- Categories heading -->
                <div class="bg-[#eef7f2] px-5 py-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67] text-white">
                            <Shapes class="h-7 w-7" />
                        </div>

                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                Categories
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                <template v-if="householdRole === 'coach'">
                                    How are the envelope balances looking?
                                </template>

                                <template v-else>
                                    How is your spending in these categories
                                    looking? Drill down to see the details.
                                </template>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Category rows -->
                <div class="space-y-3 p-5">
                    <template v-for="(heading, index) in headings" :key="heading.id">
                        <Link v-if="householdRole !== 'coach'"
                            :href="`/households/${household.id}/dashboard/categories/${heading.id}`" :class="[
                                'flex items-center gap-3 rounded-2xl border border-white/70 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                                categoryColors[
                                    index % categoryColors.length
                                ].heading,
                            ]">
                            <img v-if="heading.dashboard_image" :src="`/images/categories/${heading.dashboard_image}`"
                                :alt="heading.name" class="h-14 w-14 shrink-0 rounded-full object-cover" />

                            <div v-else class="flex h-11 w-11 shrink-0 items-center justify-center text-[#477b67]">
                                <component :is="categoryIcons[
                                    heading.icon ??
                                    'circle-dollar-sign'
                                ]
                                    " class="h-6 w-6" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="truncate font-medium uppercase text-gray-900 dark:text-white">
                                    {{ heading.name }}
                                </div>
                            </div>

                            <div class="whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                                {{
                                    Number(
                                        heading.balance ?? 0,
                                    ).toLocaleString('en-US', {
                                        style: 'currency',
                                        currency:
                                            household.default_currency ??
                                            'USD',
                                    })
                                }}
                            </div>
                        </Link>

                        <div v-else :class="[
                            'flex items-center gap-3 rounded-2xl border border-white/70 p-4 shadow-sm',
                            categoryColors[
                                index % categoryColors.length
                            ].heading,
                        ]">
                            <img v-if="heading.dashboard_image" :src="`/images/categories/${heading.dashboard_image}`"
                                :alt="heading.name" class="h-14 w-14 shrink-0 rounded-full object-cover" />

                            <div v-else class="flex h-11 w-11 shrink-0 items-center justify-center text-[#477b67]">
                                <component :is="categoryIcons[
                                    heading.icon ??
                                    'circle-dollar-sign'
                                ]
                                    " class="h-6 w-6" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="truncate font-medium uppercase text-gray-900 dark:text-white">
                                    {{ heading.name }}
                                </div>
                            </div>

                            <div class="whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                                {{
                                    Number(
                                        heading.balance ?? 0,
                                    ).toLocaleString('en-US', {
                                        style: 'currency',
                                        currency:
                                            household.default_currency ??
                                            'USD',
                                    })
                                }}
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
