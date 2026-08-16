<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Household {
    id: number;
    household_name: string;
}

interface ImportProfile {
    amount_column: string | null;
    credit_column: string | null;
    date_column: string | null;
    date_format: string | null;
    debit_column: string | null;
    description_column: string | null;
    description_field: string | null;
    format: string;
    header_signature: string | null;
    id: number;
    name: string;
    payee_field: string | null;
}

const props = defineProps<{
    household: Household;
    profile: ImportProfile;
}>();

const form = useForm({
    amount_column: props.profile.amount_column ?? '',
    credit_column: props.profile.credit_column ?? '',
    date_column: props.profile.date_column ?? '',
    date_format: props.profile.date_format ?? 'm/d/Y',
    debit_column: props.profile.debit_column ?? '',
    description_column: props.profile.description_column ?? '',
    description_field: props.profile.description_field ?? '',
    format: props.profile.format ?? 'csv',
    header_signature: props.profile.header_signature ?? '',
    name: props.profile.name,
    payee_field: props.profile.payee_field ?? '',
});

const isCsv = computed(
    () => form.format === 'csv'
);

const isOfx = computed(
    () => form.format === 'ofx'
);

function submit(): void {
    form.put(
        `/households/${props.household.id}/import-profiles/${props.profile.id}`
    );
}
</script>

<template>

    <Head :title="`Edit ${profile.name}`" />

    <div class="p-4 sm:p-6">
        <div class="mb-6">
            <Link :href="`/households/${household.id}/import-profiles`"
                class="text-sm font-medium text-[#477b67] hover:underline">
                ← Import Profiles
            </Link>

            <h1 class="mt-3 text-2xl font-semibold">
                Edit Import Profile
            </h1>

            <p class="mt-1 text-sm text-muted-foreground">
                Change how this bank transaction format is interpreted.
            </p>
        </div>

        <form class="max-w-3xl space-y-6" @submit.prevent="submit">
            <!-- Name -->
            <div>
                <label for="name" class="mb-2 block text-sm font-medium">
                    Name
                </label>

                <input id="name" v-model="form.name" type="text"
                    class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />

                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Format -->
            <div>
                <label for="format" class="mb-2 block text-sm font-medium">
                    Format
                </label>

                <select id="format" v-model="form.format"
                    class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20">
                    <option value="csv">
                        CSV
                    </option>

                    <option value="ofx">
                        OFX / QFX / QBO
                    </option>
                </select>
            </div>

            <!-- OFX -->
            <div v-if="isOfx" class="space-y-6 rounded-lg border border-gray-300 p-5">
                <div>
                    <h2 class="font-semibold">
                        OFX / QFX / QBO Fields
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Choose which OFX fields contain the payee and description.
                    </p>
                </div>

                <div>
                    <label for="payee_field" class="mb-2 block text-sm font-medium">
                        Payee Field
                    </label>

                    <select id="payee_field" v-model="form.payee_field"
                        class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20">
                        <option value="">
                            Select field
                        </option>

                        <option value="NAME">
                            NAME
                        </option>

                        <option value="MEMO">
                            MEMO
                        </option>
                    </select>
                </div>

                <div>
                    <label for="description_field" class="mb-2 block text-sm font-medium">
                        Description Field
                    </label>

                    <select id="description_field" v-model="form.description_field"
                        class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20">
                        <option value="">
                            None
                        </option>

                        <option value="NAME">
                            NAME
                        </option>

                        <option value="MEMO">
                            MEMO
                        </option>
                    </select>
                </div>
            </div>

            <!-- CSV -->
            <div v-if="isCsv" class="space-y-6 rounded-lg border border-gray-300 p-5">
                <div>
                    <h2 class="font-semibold">
                        CSV Fields
                    </h2>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Match the columns used by this CSV export.
                    </p>
                </div>

                <div>
                    <label for="header_signature" class="mb-2 block text-sm font-medium">
                        Header Signature
                    </label>

                    <input id="header_signature" v-model="form.header_signature" type="text"
                        class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />
                </div>

                <div>
                    <label for="date_column" class="mb-2 block text-sm font-medium">
                        Date Column
                    </label>

                    <input id="date_column" v-model="form.date_column" type="text"
                        class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />
                </div>

                <div>
                    <label for="description_column" class="mb-2 block text-sm font-medium">
                        Payee / Description Column
                    </label>

                    <input id="description_column" v-model="form.description_column" type="text"
                        class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />
                </div>

                <div>
                    <label for="amount_column" class="mb-2 block text-sm font-medium">
                        Amount Column
                    </label>

                    <input id="amount_column" v-model="form.amount_column" type="text"
                        class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="debit_column" class="mb-2 block text-sm font-medium">
                            Debit Column
                        </label>

                        <input id="debit_column" v-model="form.debit_column" type="text"
                            class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />
                    </div>

                    <div>
                        <label for="credit_column" class="mb-2 block text-sm font-medium">
                            Credit Column
                        </label>

                        <input id="credit_column" v-model="form.credit_column" type="text"
                            class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />
                    </div>
                </div>

                <div>
                    <label for="date_format" class="mb-2 block text-sm font-medium">
                        Date Format
                    </label>

                    <input id="date_format" v-model="form.date_format" type="text"
                        class="w-full rounded-md border border-gray-400 px-3 py-2 focus:border-[#477b67] focus:ring-2 focus:ring-[#477b67]/20" />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-[#477b67] px-4 py-2 font-medium text-white hover:opacity-90"
                    :disabled="form.processing">
                    Save Changes
                </button>

                <Link :href="`/households/${household.id}/import-profiles`"
                    class="text-sm text-muted-foreground hover:underline">
                    Cancel
                </Link>
            </div>
        </form>
    </div>
</template>
