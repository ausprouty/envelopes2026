<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CircleDollarSign, Eye, Shapes } from '@lucide/vue';
import { categoryIcons } from '@/lib/categoryIcons';
import { categoryColors } from '@/lib/categoryColors';
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
        <!-- Encouragement -->
        <div class="mb-6 rounded-3xl bg-[#477b67] px-6 py-5 text-white shadow-sm">
            <div class="text-xl font-semibold">
                Give every dollar a purpose.
            </div>

            <div class="mt-1 text-sm text-white/80">
                Then enjoy the freedom of knowing where it went.
            </div>
        </div>
    </div>

    <div class="grid gap-6 p-6 lg:grid-cols-[340px_minmax(0,1fr)]">
        <!-- Keep an eye on -->
        <div class="rounded-3xl bg-[#fbfaf6] p-5 shadow-sm">
            <div class="mb-4 flex items-start gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67] text-white">
                    <Eye class="h-7 w-7" />
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Keep an eye on
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Watched and overspent envelopes
                    </p>
                </div>
            </div>

            <div class="space-y-2">
                <div v-for="category in watchCategories" :key="category.id"
                    class="flex items-center gap-3 rounded-2xl bg-white p-3">
                    <img v-if="category.dashboard_image" :src="`/images/categories/${category.dashboard_image}`"
                        :alt="category.name" class="h-11 w-11 shrink-0 rounded-xl object-cover" />

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
                            Number(category.current_balance ?? 0).toLocaleString(
                                'en-US',
                                {
                                    style: 'currency',
                                    currency:
                                        household.default_currency ?? 'USD',
                                },
                            )
                        }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Main dashboard -->
        <div class="rounded-3xl bg-white p-5 shadow-sm dark:bg-gray-900">
            <!-- Available -->
            <div class="mb-6 flex items-start gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67] text-white">
                    <CircleDollarSign class="h-7 w-7" />
                </div>

                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Available this month
                    </div>

                    <div class="text-3xl font-semibold text-gray-900 dark:text-white">
                        {{
                            Number(totalAvailable ?? 0).toLocaleString('en-US', {
                                style: 'currency',
                                currency: household.default_currency ?? 'USD',
                            })
                        }}
                    </div>
                </div>
            </div>

            <!-- Categories heading -->
            <div class="mb-4 flex items-start gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#477b67] text-white">
                    <Shapes class="h-7 w-7" />
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Categories
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        How is your spending in these categories looking?
                        Drill down to see the details.
                    </p>
                </div>
            </div>

            <!-- Category rows -->
            <div class="space-y-2">
                <div v-for="(heading, index) in headings" :key="heading.id" :class="[
                    'flex items-center gap-3 rounded-2xl p-3 transition hover:shadow-sm',
                    categoryColors[index % categoryColors.length].heading,
                ]">
                    <img v-if="heading.dashboard_image" :src="`/images/categories/${heading.dashboard_image}`"
                        :alt="heading.name" class="h-11 w-11 shrink-0 rounded-xl object-cover" />

                    <div v-else class="flex h-11 w-11 shrink-0 items-center justify-center text-[#111111]">
                        <component :is="categoryIcons[
                            heading.icon ?? 'circle-dollar-sign'
                        ]
                            " class="h-5 w-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium text-gray-900 dark:text-white">
                            {{ heading.name }}
                        </div>
                    </div>

                    <div class="whitespace-nowrap font-semibold text-gray-900 dark:text-white">
                        {{
                            Number(heading.balance ?? 0).toLocaleString('en-US', {
                                style: 'currency',
                                currency: household.default_currency ?? 'USD',
                            })
                        }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
