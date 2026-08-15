<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    household: {
        id: number;
        household_name: string;
    };

    profiles: Array<{
        id: number;
        name: string;
        payee_field: string | null;
        description_field: string | null;
        header_signature: string | null;
        date_column: string | null;
        amount_column: string | null;
        debit_column: string | null;
        credit_column: string | null;
        date_format: string | null;
    }>;
}>();
</script>

<template>

    <Head title="Import Profiles" />

    <div class="p-4 sm:p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    Import Profiles
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Define how bank transaction files should be interpreted.
                </p>
            </div>

            <Link :href="`/households/${props.household.id}/import-profiles/create`"
                class="rounded-md bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                New Import Profile
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg border bg-white">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">
                            Name
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Payee Field
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Description Field
                        </th>

                        <th class="px-4 py-3 text-left font-medium">
                            Date Format
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="profile in profiles" :key="profile.id" class="border-b last:border-b-0">
                        <td class="px-4 py-3 font-medium">
                            {{ profile.name }}
                        </td>

                        <td class="px-4 py-3">
                            {{ profile.payee_field ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ profile.description_field ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ profile.date_format ?? '—' }}
                        </td>
                    </tr>

                    <tr v-if="profiles.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                            No import profiles yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
