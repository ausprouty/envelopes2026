<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { route } from '@/lib/route';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
);

interface Household {
    id: number;
    household_name: string;
}

interface Account {
    id: number;
    account_name: string;
    institution_name: string | null;
    account_type: string;
    currency: string;
}

interface BalanceHistory {
    id: number;
    balance_date: string;
    ledger_balance: number | string;
}

const props = defineProps<{
    household: Household;
    account: Account;
    history: BalanceHistory[];
}>();

function parseDate(date: string): Date {
    return new Date(`${date.substring(0, 10)}T00:00:00`);
}

function formatDate(date: string): string {
    return new Intl.DateTimeFormat('en-AU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(parseDate(date));
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: props.account.currency,
        maximumFractionDigits: 0,
    }).format(value);
}

const chartData = computed(() => ({
    labels: props.history.map((item) => formatDate(item.balance_date)),

    datasets: [
        {
            label: 'Ledger Balance',
            data: props.history.map((item) =>
                Number(item.ledger_balance),
            ),
            tension: 0.2,
            pointRadius: 5,
            pointHoverRadius: 7,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,

    plugins: {
        legend: {
            display: false,
        },

        tooltip: {
            callbacks: {
                label: (context: any) =>
                    formatCurrency(context.parsed.y),
            },
        },
    },

    scales: {
        y: {
            ticks: {
                callback: (value: string | number) =>
                    formatCurrency(Number(value)),
            },
        },
    },
}));
</script>

<template>
    <Head :title="`${account.account_name} - Balance History`" />

    <div class="p-6">
        <div class="mb-6">
            <Link
                :href="route('households.balances.index', {
                    household: household.id,
                })"
                class="text-sm text-blue-600 hover:text-blue-800"
            >
                ← Account Balances
            </Link>

            <h1 class="mt-2 text-2xl font-semibold">
                {{ account.account_name }} — Balance History
            </h1>

            <p
                v-if="account.institution_name"
                class="mt-1 text-gray-600"
            >
                {{ account.institution_name }}
            </p>
        </div>

        <div
            v-if="history.length"
            class="max-w-5xl"
        >
            <div class="mb-8 h-96 rounded-lg border bg-white p-4">
                <Line
                    :data="chartData"
                    :options="chartOptions"
                />
            </div>

            <h2 class="mb-3 text-lg font-semibold">
                Balance Records
            </h2>

            <table class="w-full max-w-xl">
                <thead>
                    <tr class="border-b text-left">
                        <th class="py-2">
                            Date
                        </th>
                        <th class="py-2 text-right">
                            Balance
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="item in [...history].reverse()"
                        :key="item.id"
                        class="border-b"
                    >
                        <td class="py-2">
                            {{ formatDate(item.balance_date) }}
                        </td>

                        <td class="py-2 text-right">
                            {{
                                formatCurrency(
                                    Number(item.ledger_balance),
                                )
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-else
            class="text-gray-500"
        >
            No balance history has been recorded in the last
            24 months.
        </div>
    </div>
</template>
