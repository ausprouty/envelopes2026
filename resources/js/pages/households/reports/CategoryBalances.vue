<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { categoryColors } from '@/lib/categoryColors';

type CategoryRow = {
    id: number;
    name: string;
    balance: number;
};

type Heading = {
    id: number;
    name: string;
    balance: number;
    categories: CategoryRow[];
};

const props = defineProps<{
    household: {
        id: number;
        household_name: string;
        default_currency: string;
    };
    headings: Heading[];
    totalBalance: number;
    reportContext: 'household' | 'ministry_au';
    hasAuMinistryCategories: boolean;
}>();

function changeContext(context: 'household' | 'ministry_au') {
    router.get(
        `/households/${props.household.id}/reports/category-balances`,
        {
            context,
        },
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}

function formatAmount(amount: number) {
    return Number(amount).toLocaleString('en-AU', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>

<template>

    <Head title="Category Balances" />

    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <!-- HEADING -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-gray-900">
                    Category Balances
                </h1>

                <p class="mt-1 text-lg text-gray-500">
                    {{ household.household_name }}
                </p>
            </div>

            <!-- PERSONAL / MINISTRY -->
            <div v-if="hasAuMinistryCategories" class="flex gap-2">
                <button type="button" class="rounded-md border px-4 py-2 font-medium shadow-sm" :class="reportContext === 'household'
                        ? 'border-[#477b67] bg-[#477b67] text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                    " @click="changeContext('household')">
                    Personal
                </button>

                <button type="button" class="rounded-md border px-4 py-2 font-medium shadow-sm" :class="reportContext === 'ministry_au'
                        ? 'border-[#477b67] bg-[#477b67] text-white'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                    " @click="changeContext('ministry_au')">
                    Ministry
                </button>
            </div>
        </div>

        <!-- TOTAL -->
        <div class="rounded-xl border border-gray-300 bg-white px-6 py-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-500">
                        Total balance
                    </div>

                    <div class="mt-1 text-sm text-gray-500">
                        {{
                            reportContext === 'household'
                                ? 'Personal categories'
                                : 'Ministry categories'
                        }}
                    </div>
                </div>

                <div class="text-2xl font-semibold" :class="totalBalance < 0
                        ? 'text-red-600'
                        : 'text-gray-900'
                    ">
                    {{ household.default_currency }}
                    {{ formatAmount(totalBalance) }}
                </div>
            </div>
        </div>

        <!-- CATEGORY GROUPS -->
        <div class="space-y-5">
            <div v-for="(heading, index) in headings" :key="heading.id"
                class="overflow-hidden rounded-xl border border-gray-300 shadow-sm">
                <!-- HEADING ROW -->
                <div class="flex items-center justify-between px-5 py-4" :class="categoryColors[
                        index % categoryColors.length
                    ].heading
                    ">
                    <div class="text-lg font-semibold text-gray-900">
                        {{ heading.name }}
                    </div>

                    <div class="font-semibold" :class="heading.balance < 0
                            ? 'text-red-600'
                            : 'text-gray-900'
                        ">
                        {{ household.default_currency }}
                        {{ formatAmount(heading.balance) }}
                    </div>
                </div>

                <!-- CHILD CATEGORIES -->
                <div>
                    <Link v-for="category in heading.categories" :key="category.id"
                        :href="`/households/${household.id}/dashboard/envelopes/${category.id}`"
                        class="flex items-center justify-between border-t border-gray-300 px-5 py-3 transition hover:brightness-[0.98]"
                        :class="categoryColors[
                                index % categoryColors.length
                            ].child
                            ">
                        <div class="text-sm font-medium text-gray-800">
                            {{ category.name }}
                        </div>

                        <div class="text-sm font-semibold" :class="category.balance < 0
                                ? 'text-red-600'
                                : 'text-gray-900'
                            ">
                            {{ household.default_currency }}
                            {{ formatAmount(category.balance) }}
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="headings.length === 0"
            class="rounded-xl border border-gray-300 bg-white p-8 text-center text-gray-500">
            No category balances found.
        </div>
    </div>
</template>
