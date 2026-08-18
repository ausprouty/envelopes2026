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

const categoryImage = (
    filename: string | null,
): string | null => {
    return filename
        ? `/images/categories/${filename}`
        : null;
};
</script>

<template>

    <Head :title="category.name" />

    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <Link :href="`/households/${household.id}/dashboard`"
            class="inline-flex items-center gap-2 text-sm font-medium text-[#477b67] hover:underline">
            ← Dashboard
        </Link>

        <!-- CATEGORY HEADER -->
        <div class="rounded-2xl bg-[#477b67] p-6 text-white shadow-sm">
            <div class="flex items-center gap-4">
                <div v-if="category.dashboard_image" class="h-16 w-16 overflow-hidden rounded-xl bg-white/20">
                    <img :src="categoryImage(category.dashboard_image) ?? ''" :alt="category.name"
                        class="h-full w-full object-cover" />
                </div>

                <div>
                    <h1 class="text-3xl font-semibold">
                        {{ category.name }}
                    </h1>

                    <p class="mt-1 text-sm text-white/80">
                        Your envelopes in this category.
                    </p>
                </div>
            </div>
        </div>

        <!-- ENVELOPES -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <Link v-for="(envelope, index) in envelopes" :key="envelope.id"
                :href="`/households/${household.id}/dashboard/envelopes/${envelope.id}`"
                class="flex items-center justify-between gap-4 px-6 py-5 transition hover:bg-[#f3f8f5]" :class="{
                    'border-t border-gray-200': index > 0,
                }">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-[#edf5f1]">
                        <img v-if="envelope.dashboard_image" :src="categoryImage(envelope.dashboard_image) ?? ''"
                            :alt="envelope.name" class="h-full w-full object-cover" />

                        <span v-else class="text-lg font-semibold text-[#477b67]">
                            $
                        </span>
                    </div>

                    <div class="text-lg font-medium text-gray-900">
                        {{ envelope.name }}
                    </div>
                </div>

                <div class="text-lg font-semibold" :class="envelope.balance < 0
                    ? 'text-red-600'
                    : 'text-gray-900'
                    ">
                    {{
                        Number(envelope.balance).toLocaleString('en-AU', {
                            style: 'currency',
                            currency: household.default_currency,
                        })
                    }}
                </div>
            </Link>
        </div>
    </div>
</template>
