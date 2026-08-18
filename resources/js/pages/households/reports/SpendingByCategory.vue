<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Household {
    id: number;
    household_name: string;
    default_currency: string;
}

interface MonthData {
    month: number;
    actual: number;
    difference: number;
    percent: number | null;
    status: 'normal' | 'over' | 'under';
}

interface ReportRow {
    id: number;
    parent_category_id: number | null;
    name: string;
    code: string | null;
    category_type: 'heading' | 'expense';
    usual_allocation: number;
    selected_month: MonthData | null;
    dashboard_image: string | null;
    months: MonthData[];
    year_total: number;
    average: number;
}

type PageProps = {
    auth?: {
        user?: {
            households?: {
                id: number;
                pivot?: {
                    role?: 'coach' | 'member';
                };
            }[];
            role?: 'admin' | 'user';
        };
    };
};

const props = defineProps<{
    household: Household;
    context: 'household' | 'ministry_au';
    view: 'month' | 'year';
    year: number;
    month: number;
    rows: ReportRow[];
}>();

const page = usePage<PageProps>();

const canDrillDown = computed(() => {
    return page.props.auth?.user?.households?.[0]?.pivot?.role !== 'coach';
});



const monthNames = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
];

const shortMonthNames = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
];

const expenseRows = computed(() =>
    props.rows.filter((row) => row.category_type === 'expense'),
);

const monthlyOverCount = computed(() =>
    expenseRows.value.filter(
        (row) => row.selected_month?.status === 'over',
    ).length,
);

const monthlyUnderCount = computed(() =>
    expenseRows.value.filter(
        (row) => row.selected_month?.status === 'under',
    ).length,
);

const monthlyNormalCount = computed(() =>
    expenseRows.value.filter(
        (row) => row.selected_month?.status === 'normal',
    ).length,
);

const monthlyAllocationTotal = computed(() =>
    expenseRows.value.reduce(
        (total, row) => total + row.usual_allocation,
        0,
    ),
);

const monthlyActualTotal = computed(() =>
    expenseRows.value.reduce(
        (total, row) => total + (row.selected_month?.actual ?? 0),
        0,
    ),
);

const yearlyTotal = computed(() =>
    expenseRows.value.reduce(
        (total, row) => total + row.year_total,
        0,
    ),
);

function reportUrl(options: {
    context?: 'household' | 'ministry_au';
    view?: 'month' | 'year';
    year?: number;
    month?: number;
}) {
    const params = new URLSearchParams({
        context: options.context ?? props.context,
        view: options.view ?? props.view,
        year: String(options.year ?? props.year),
        month: String(options.month ?? props.month),
    });

    return `/households/${props.household.id}/reports/spending-by-category?${params.toString()}`;
}

function previousPeriod() {
    if (props.view === 'year') {
        router.get(
            reportUrl({
                year: props.year - 1,
            }),
        );

        return;
    }

    let month = props.month - 1;
    let year = props.year;

    if (month < 1) {
        month = 12;
        year--;
    }

    router.get(
        reportUrl({
            month,
            year,
        }),
    );
}
function imageUrl(path: string | null) {
    if (!path) {
        return null;
    }

    if (path.startsWith('/')) {
        return path;
    }

    return `/images/categories/${path}`;
}

function nextPeriod() {
    if (props.view === 'year') {
        router.get(
            reportUrl({
                year: props.year + 1,
            }),
        );

        return;
    }

    let month = props.month + 1;
    let year = props.year;

    if (month > 12) {
        month = 1;
        year++;
    }

    router.get(
        reportUrl({
            month,
            year,
        }),
    );
}

function formatMoney(value: number) {
    return new Intl.NumberFormat('en-AU', {
        style: 'currency',
        currency: props.household.default_currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

function formatPercent(value: number | null) {
    if (value === null) {
        return '—';
    }

    return `${Math.abs(Math.round(value))}%`;
}

function statusClass(status: MonthData['status']) {
    if (status === 'over') {
        return 'font-bold text-red-700';
    }

    if (status === 'under') {
        return 'font-bold text-amber-700';
    }

    return 'text-gray-600';
}

function groupNumberForRow(rowIndex: number) {
    let group = -1;

    for (let index = 0; index <= rowIndex; index++) {
        if (props.rows[index].category_type === 'heading') {
            group++;
        }
    }

    return Math.max(group, 0);
}

function rowBackground(row: ReportRow, rowIndex: number) {
    const palettes = [
        {
            heading: 'bg-emerald-50',
            child: 'bg-emerald-50/40',
        },
        {
            heading: 'bg-amber-100/70',
            child: 'bg-amber-50/50',
        },
        {
            heading: 'bg-fuchsia-100/60',
            child: 'bg-fuchsia-50/40',
        },
        {
            heading: 'bg-sky-100/60',
            child: 'bg-sky-50/40',
        },
    ];

    const palette =
        palettes[groupNumberForRow(rowIndex) % palettes.length];

    return row.category_type === 'heading'
        ? palette.heading
        : palette.child;
}
</script>

<template>

    <Head title="Spending by Category" />

    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-[1500px]">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <Link :href="`/households/${household.id}/reports`"
                        class="mb-2 inline-block text-sm font-medium text-[#477b67] hover:underline">
                        ← Reports
                    </Link>

                    <h1 class="text-2xl font-bold text-gray-900">
                        Spending by Category
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ household.household_name }}
                    </p>
                </div>

                <!-- Personal / Ministry -->
                <div class="inline-flex self-start rounded-lg border border-gray-200 bg-white p-1">
                    <Link :href="reportUrl({
                        context: 'household',
                    })
                        " class="rounded-md px-4 py-2 text-sm font-medium" :class="context === 'household'
                            ? 'bg-[#477b67] text-white'
                            : 'text-gray-600 hover:bg-gray-50'
                            ">
                        Personal
                    </Link>

                    <Link :href="reportUrl({
                        context: 'ministry_au',
                    })
                        " class="rounded-md px-4 py-2 text-sm font-medium" :class="context === 'ministry_au'
                            ? 'bg-[#477b67] text-white'
                            : 'text-gray-600 hover:bg-gray-50'
                            ">
                        Ministry
                    </Link>
                </div>
            </div>

            <!-- Controls -->
            <div
                class="mb-6 flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Month / Year -->
                <div class="inline-flex self-start rounded-lg bg-gray-100 p-1">
                    <Link :href="reportUrl({
                        view: 'month',
                    })
                        " class="rounded-md px-4 py-2 text-sm font-medium" :class="view === 'month'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500'
                            ">
                        Month
                    </Link>

                    <Link :href="reportUrl({
                        view: 'year',
                    })
                        " class="rounded-md px-4 py-2 text-sm font-medium" :class="view === 'year'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-500'
                            ">
                        Year
                    </Link>
                </div>

                <!-- Period navigation -->
                <div class="flex items-center gap-3">
                    <button type="button"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-gray-600 hover:bg-gray-50"
                        @click="previousPeriod">
                        ←
                    </button>

                    <div class="min-w-44 text-center font-semibold text-gray-900">
                        <template v-if="view === 'month'">
                            {{ monthNames[month - 1] }} {{ year }}
                        </template>

                        <template v-else>
                            {{ year }}
                        </template>
                    </div>

                    <button type="button"
                        class="rounded-lg border border-gray-200 px-3 py-2 text-gray-600 hover:bg-gray-50"
                        @click="nextPeriod">
                        →
                    </button>
                </div>
            </div>

            <!-- Monthly summary -->
            <div v-if="view === 'month'" class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-medium uppercase text-gray-500">
                        Usual allocation
                    </div>

                    <div class="mt-1 text-xl font-semibold">
                        {{ formatMoney(monthlyAllocationTotal) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-medium uppercase text-gray-500">
                        Actual spending
                    </div>

                    <div class="mt-1 text-xl font-semibold">
                        {{ formatMoney(monthlyActualTotal) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-medium uppercase text-gray-500">
                        Over 20%
                    </div>

                    <div class="mt-1 text-xl font-bold text-red-700">
                        ↑ {{ monthlyOverCount }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-medium uppercase text-gray-500">
                        Under 20%
                    </div>

                    <div class="mt-1 text-xl font-bold text-amber-700">
                        ↓ {{ monthlyUnderCount }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-medium uppercase text-gray-500">
                        In range
                    </div>

                    <div class="mt-1 text-xl font-semibold text-gray-700">
                        {{ monthlyNormalCount }}
                    </div>
                </div>
            </div>

            <!-- Yearly summary -->
            <div v-else class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-medium uppercase text-gray-500">
                        Total spending
                    </div>

                    <div class="mt-1 text-xl font-semibold">
                        {{ formatMoney(yearlyTotal) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="text-xs font-medium uppercase text-gray-500">
                        Normal monthly allocation
                    </div>

                    <div class="mt-1 text-xl font-semibold">
                        {{ formatMoney(monthlyAllocationTotal) }}
                    </div>
                </div>
            </div>

            <!-- Monthly Table -->
            <div v-if="view === 'month'" class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px]">
                        <thead
                            class="border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">
                                    Category
                                </th>

                                <th class="px-4 py-3 bg-slate-200/70 text-right">
                                    Usual
                                </th>

                                <th class="px-4 py-3 bg-slate-200/70 text-right">
                                    Actual
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Difference
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Change
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(row, rowIndex) in rows" :key="row.id"
                                class="border-b border-gray-100 last:border-b-0" :class="rowBackground(row, rowIndex)">
                                <template v-if="row.category_type === 'heading'">
                                    <td colspan="5" class="px-4 py-3 font-bold text-gray-900">
                                        {{ row.name }}
                                    </td>
                                </template>

                                <template v-else>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img v-if="row.dashboard_image" :src="imageUrl(row.dashboard_image) ?? ''"
                                                :alt="row.name" class="h-8 w-8 rounded-md object-cover" />

                                            <div v-else
                                                class="flex h-8 w-8 items-center justify-center rounded-md bg-gray-100 text-xs text-gray-400">
                                                •
                                            </div>

                                            <Link v-if="canDrillDown"
                                                :href="`/households/${household.id}/categories/${row.id}`"
                                                class="font-medium text-gray-900 hover:text-[#477b67] hover:underline">
                                                {{ row.name }}
                                            </Link>

                                            <span v-else class="font-medium text-gray-900">
                                                {{ row.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-right bg-slate-100/70 tabular-nums text-gray-600">
                                        {{
                                            formatMoney(
                                                row.usual_allocation,
                                            )
                                        }}
                                    </td>

                                    <td class="px-4 py-3 text-right bg-slate-100/70 tabular-nums" :class="row.selected_month &&
                                        row.selected_month.status !==
                                        'normal'
                                        ? 'font-bold text-gray-900'
                                        : 'text-gray-700'
                                        ">
                                        {{
                                            formatMoney(
                                                row.selected_month
                                                    ?.actual ?? 0,
                                            )
                                        }}
                                    </td>

                                    <td class="px-4 py-3 text-right tabular-nums text-gray-600">
                                        {{
                                            formatMoney(
                                                row.selected_month
                                                    ?.difference ?? 0,
                                            )
                                        }}
                                    </td>

                                    <td class="px-4 py-3 text-right tabular-nums">
                                        <span v-if="
                                            row.selected_month
                                                ?.status === 'over'
                                        " class="font-bold text-red-700">
                                            ↑
                                            {{
                                                formatPercent(
                                                    row.selected_month
                                                        .percent,
                                                )
                                            }}
                                        </span>

                                        <span v-else-if="
                                            row.selected_month
                                                ?.status === 'under'
                                        " class="font-bold text-amber-700">
                                            ↓
                                            {{
                                                formatPercent(
                                                    row.selected_month
                                                        .percent,
                                                )
                                            }}
                                        </span>

                                        <span v-else class="text-gray-500">
                                            {{
                                                formatPercent(
                                                    row.selected_month
                                                        ?.percent ??
                                                    null,
                                                )
                                            }}
                                        </span>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Yearly Table -->
            <div v-else class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1350px]">
                        <thead
                            class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="sticky left-0 z-10 bg-gray-50 px-4 py-3 text-left">
                                    Category
                                </th>
                                <th class="bg-gray-50 px-3 py-3 text-right">
                                    Usual
                                </th>

                                <th class="bg-gray-50 px-3 py-3 text-right">
                                    Avg
                                </th>
                                <th v-for="monthName in shortMonthNames" :key="monthName" class="px-3 py-3 text-right">
                                    {{ monthName }}
                                </th>

                                <th class="px-3 py-3 text-right">
                                    Total
                                </th>



                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(row, rowIndex) in rows" :key="row.id"
                                class="border-b border-gray-100 last:border-b-0" :class="rowBackground(row, rowIndex)">
                                <template v-if="row.category_type === 'heading'">
                                    <td colspan="16" class="px-4 py-3 font-bold text-gray-900">
                                        {{ row.name }}
                                    </td>
                                </template>

                                <template v-else>

                                    <td class="sticky left-0 z-10 px-4 py-3" :class="rowBackground(row, rowIndex)">
                                        <div class="flex items-center gap-3">
                                            <img v-if="row.dashboard_image" :src="imageUrl(row.dashboard_image) ?? ''"
                                                :alt="row.name" class="h-8 w-8 rounded-md object-cover" />

                                            <div v-else
                                                class="flex h-8 w-8 items-center justify-center rounded-md bg-white/60 text-xs text-gray-400">
                                                •
                                            </div>

                                            <Link v-if="canDrillDown"
                                                :href="`/households/${household.id}/categories/${row.id}`"
                                                class="font-medium text-gray-900 hover:text-[#477b67] hover:underline">
                                                {{ row.name }}
                                            </Link>

                                            <span v-else class="font-medium text-gray-900">
                                                {{ row.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class=" px-3 py-3 bg-slate-200/70 text-right font-semibold tabular-nums text-gray-700">
                                        {{ formatMoney(row.usual_allocation) }}
                                    </td>

                                    <td
                                        class=" px-3 py-3 bg-slate-200/70 text-right font-semibold tabular-nums text-gray-700">
                                        {{ formatMoney(row.average) }}
                                    </td>

                                    <td v-for="monthData in row.months" :key="monthData.month"
                                        class="px-3 py-3 text-right">
                                        <div class="text-sm tabular-nums text-gray-800" :class="monthData.status !==
                                            'normal'
                                            ? 'font-bold'
                                            : ''
                                            ">
                                            {{
                                                formatMoney(
                                                    monthData.actual,
                                                )
                                            }}
                                        </div>

                                        <div v-if="
                                            monthData.status ===
                                            'over'
                                        " class="mt-0.5 text-xs font-bold text-red-700">
                                            ↑
                                            {{
                                                formatPercent(
                                                    monthData.percent,
                                                )
                                            }}
                                        </div>

                                        <div v-else-if="
                                            monthData.status ===
                                            'under'
                                        " class="mt-0.5 text-xs font-bold text-amber-700">
                                            ↓
                                            {{
                                                formatPercent(
                                                    monthData.percent,
                                                )
                                            }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-3 text-right  font-semibold tabular-nums">
                                        {{ formatMoney(row.year_total) }}
                                    </td>


                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-4 text-sm text-gray-500">
                ↑ more than 20% above the usual allocation ·
                ↓ more than 20% below the usual allocation
            </p>
        </div>
    </div>
</template>
