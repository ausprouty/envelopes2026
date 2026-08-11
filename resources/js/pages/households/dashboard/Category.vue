<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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

defineProps<{
    household: Household;
    category: Category;
    envelopes: Envelope[];
}>();
</script>

<template>

    <Head :title="category.name" />

    <div class="p-6">
        <div class="mb-6">
            <Link :href="`/households/${household.id}/dashboard`"
                class="text-sm font-medium text-[#477b67] hover:underline">
                ← Back to dashboard
            </Link>
        </div>

        <div class="mb-6 flex items-center gap-4">
            <img v-if="category.dashboard_image" :src="`/images/categories/${category.dashboard_image}`"
                :alt="category.name" class="h-16 w-16 rounded-full object-cover" />

            <div>
                <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ category.name }}
                </h1>

                <p class="mt-1 text-gray-500 dark:text-gray-400">
                    See how each envelope in this category is doing.
                </p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link v-for="envelope in envelopes" :key="envelope.id"
                :href="`/households/${household.id}/dashboard/envelopes/${envelope.id}`"
                class="group flex items-center gap-4 rounded-3xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
                <img v-if="envelope.dashboard_image" :src="`/images/categories/${envelope.dashboard_image}`"
                    :alt="envelope.name" class="h-14 w-14 shrink-0 rounded-full object-cover" />

                <div class="min-w-0 flex-1">
                    <div class="truncate text-lg font-medium text-gray-900 dark:text-white">
                        {{ envelope.name }}
                    </div>

                    <div v-if="envelope.needs_attention" class="mt-1 text-xs font-medium text-[#477b67]">
                        Watching
                    </div>
                </div>

                <div class="whitespace-nowrap text-lg font-semibold" :class="envelope.balance < 0
                        ? 'text-red-600'
                        : 'text-gray-900 dark:text-white'
                    ">
                    {{
                        Number(envelope.balance ?? 0).toLocaleString('en-US', {
                            style: 'currency',
                            currency: household.default_currency ?? 'USD',
                        })
                    }}
                </div>
            </Link>
        </div>
    </div>
</template>
