<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CircleDollarSign, Eye, Shapes } from '@lucide/vue';
import { categoryColors } from '@/lib/categoryColors';
import { categoryIcons } from '@/lib/categoryIcons';
import type { CategoryIconName } from '@/lib/categoryIcons';


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

defineProps<{
    household: {
        id: number;
        household_name: string;
        default_currency: string;
    };
    headings: Heading[];
    totalAvailable: number;
    watchCategories: WatchCategory[];
}>();



</script>

<template>

    <Head title="Dashboard" />

    <div class="p-6">
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
                            Number(totalAvailable ?? 0).toLocaleString('en-US', {
                                style: 'currency',
                                currency: household.default_currency ?? 'USD',
                            })
                        }}
                    </div>
                </div>
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
                    <Link v-for="category in watchCategories" :key="category.id"
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
                                        household.default_currency ?? 'USD',
                                })
                            }}
                        </div>
                    </Link>
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
                                How is your spending in these categories looking?
                                Drill down to see the details.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Category rows -->
                <div class="space-y-3 p-5">
                    <Link v-for="(heading, index) in headings" :key="heading.id"
                        :href="`/households/${household.id}/dashboard/categories/${heading.id}`" :class="[
                            'flex items-center gap-3 rounded-2xl border border-white/70 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                            categoryColors[index % categoryColors.length].heading,
                        ]">
                        <img v-if="heading.dashboard_image" :src="`/images/categories/${heading.dashboard_image}`"
                            :alt="heading.name" class="h-14 w-14 shrink-0 rounded-full object-cover" />

                        <div v-else class="flex h-11 w-11 shrink-0 items-center justify-center text-[#477b67]">
                            <component :is="categoryIcons[
                                heading.icon ?? 'circle-dollar-sign'
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
                                Number(heading.balance ?? 0).toLocaleString(
                                    'en-US',
                                    {
                                        style: 'currency',
                                        currency:
                                            household.default_currency ?? 'USD',
                                    },
                                )
                            }}
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
