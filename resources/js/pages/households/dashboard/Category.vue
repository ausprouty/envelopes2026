<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Eye } from '@lucide/vue';

type Household = {
    id: number;
    household_name: string;
    default_currency: string;
};

type Category = {
    id: number;
    name: string;
    dashboard_image: string | null;
};

type Envelope = {
    id: number;
    name: string;
    dashboard_image: string | null;
    balance: number;
    needs_attention: boolean;
};

const props = defineProps<{
    household: Household;
    category: Category;
    envelopes: Envelope[];
}>();

const money = (amount: number) => {
    return Number(amount ?? 0).toLocaleString('en-US', {
        style: 'currency',
        currency: props.household.default_currency ?? 'USD',
    });
};
</script>

<template>

    <Head :title="category.name" />

    <div class="p-4 sm:p-6">
        <!-- Back -->
        <div class="mb-5">
            <Link :href="`/households/${household.id}/dashboard`"
                class="inline-flex items-center gap-2 text-sm font-medium text-[#477b67] hover:underline">
                <ArrowLeft class="h-4 w-4" />
                Dashboard
            </Link>
        </div>

        <!-- Heading -->
        <div class="mb-6 flex items-center gap-4">
            <img v-if="category.dashboard_image" :src="`/images/categories/${category.dashboard_image}`"
                :alt="category.name" class="h-16 w-16 shrink-0 rounded-full object-cover" />

            <div>
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ category.name }}
                </h1>

                <p class="mt-1 text-gray-500 dark:text-gray-400">
                    Your envelopes in this category.
                </p>
            </div>
        </div>

        <!-- Envelope list -->
        <div
            class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div v-if="envelopes.length === 0" class="px-6 py-10 text-center text-gray-500">
                No envelopes in this category.
            </div>

            <div v-else class="divide-y divide-gray-100 dark:divide-gray-800">
                <Link v-for="envelope in envelopes" :key="envelope.id"
                    :href="`/households/${household.id}/dashboard/envelopes/${envelope.id}`"
                    class="group flex items-center gap-4 px-5 py-4 transition hover:bg-[#f7fbf9] dark:hover:bg-gray-800 sm:px-6">
                    <!-- Image -->
                    <img v-if="envelope.dashboard_image" :src="`/images/categories/${envelope.dashboard_image}`"
                        :alt="envelope.name" class="h-12 w-12 shrink-0 rounded-full object-cover" />

                    <!-- Name -->
                    <div class="min-w-0 flex-1">
                        <div class="text-base font-medium text-gray-900 dark:text-white sm:text-lg">
                            {{ envelope.name }}
                        </div>

                        <div v-if="envelope.needs_attention"
                            class="mt-1 flex items-center gap-1 text-xs font-medium text-[#477b67]">
                            <Eye class="h-3.5 w-3.5" />
                            Watching
                        </div>
                    </div>

                    <!-- Balance -->
                    <div class="shrink-0 text-right text-base font-semibold sm:text-lg" :class="envelope.balance < 0
                            ? 'text-red-600'
                            : 'text-gray-900 dark:text-white'
                        ">
                        {{ money(envelope.balance) }}
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
