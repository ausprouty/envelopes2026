<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Household {
    id: number;
    household_name: string;
    default_currency: string;
}

interface Account {
    id: number;
    legacy_paidby_id: number | null;
    account_name: string;
    institution_name: string | null;
    account_type: string;
    currency: string;
    account_reference: string | null;
    website: string | null;
    warning_balance: number | null;
    is_active: boolean;
}

const props = defineProps<{
    household: Household;
    account: Account | null;
}>();

const form = useForm({
    legacy_paidby_id: props.account?.legacy_paidby_id ?? '',
    account_name: props.account?.account_name ?? '',
    institution_name: props.account?.institution_name ?? '',
    account_type: props.account?.account_type ?? 'checking',
    currency:
        props.account?.currency ??
        props.household.default_currency,
    account_reference: props.account?.account_reference ?? '',
    website: props.account?.website ?? '',
    warning_balance: props.account?.warning_balance ?? '',
    is_active: props.account?.is_active ?? true,
});

function submit() {
    if (props.account) {
        form.put(
            `/households/${props.household.id}/accounts/${props.account.id}`,
        );
    } else {
        form.post(
            `/households/${props.household.id}/accounts`,
        );
    }
}
</script>

<template>

    <Head :title="account ? 'Edit Financial Account' : 'Add Financial Account'" />

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6">
                <Link :href="`/households/${household.id}/accounts`"
                    class="text-sm font-medium text-[#477b67] hover:underline">
                    ← Back to accounts
                </Link>

                <h1 class="mt-3 text-2xl font-bold text-gray-900">
                    {{ account ? 'Edit Financial Account' : 'Add Financial Account' }}
                </h1>

                <p class="mt-1 text-sm text-gray-600">
                    {{ household.household_name }}
                </p>
            </div>

            <form class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
                <div class="space-y-6">
                    <!-- Account name -->
                    <div>
                        <label for="account_name" class="block text-sm font-medium text-gray-700">
                            Account Name
                        </label>

                        <input id="account_name" v-model="form.account_name" type="text"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]" />

                        <p v-if="form.errors.account_name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.account_name }}
                        </p>
                    </div>

                    <!-- Institution -->
                    <div>
                        <label for="institution_name" class="block text-sm font-medium text-gray-700">
                            Institution
                        </label>

                        <input id="institution_name" v-model="form.institution_name" type="text"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]" />

                        <p v-if="form.errors.institution_name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.institution_name }}
                        </p>
                    </div>

                    <!-- Account type -->
                    <div>
                        <label for="account_type" class="block text-sm font-medium text-gray-700">
                            Account Type
                        </label>

                        <select id="account_type" v-model="form.account_type"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]">
                            <option value="cash">Cash</option>
                            <option value="checking">Checking</option>
                            <option value="savings">Savings</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="term_deposit">Term Deposit</option>
                            <option value="investment">Investment</option>
                            <option value="retirement">Retirement</option>
                            <option value="superannuation">Superannuation</option>
                            <option value="crypto">Crypto</option>
                            <option value="reimbursement">
                                Reimbursement
                            </option>
                            <option value="ministry">Ministry</option>
                            <option value="virtual">Virtual</option>
                            <option value="other">Other</option>
                        </select>

                        <p v-if="form.errors.account_type" class="mt-1 text-sm text-red-600">
                            {{ form.errors.account_type }}
                        </p>
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700">
                            Currency
                        </label>

                        <input id="currency" v-model="form.currency" type="text" maxlength="3"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 uppercase shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]" />

                        <p class="mt-1 text-xs text-gray-500">
                            Example: USD or AUD
                        </p>

                        <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600">
                            {{ form.errors.currency }}
                        </p>
                    </div>

                    <!-- Account reference -->
                    <div>
                        <label for="account_reference" class="block text-sm font-medium text-gray-700">
                            Account Reference
                        </label>

                        <input id="account_reference" v-model="form.account_reference" type="text"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]" />

                        <p class="mt-1 text-xs text-gray-500">
                            For example, the last four digits of the account.
                        </p>

                        <p v-if="form.errors.account_reference" class="mt-1 text-sm text-red-600">
                            {{ form.errors.account_reference }}
                        </p>
                    </div>

                    <!-- Website -->
                    <div>
                        <label for="website" class="block text-sm font-medium text-gray-700">
                            Website
                        </label>

                        <input id="website" v-model="form.website" type="url"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]" />

                        <p v-if="form.errors.website" class="mt-1 text-sm text-red-600">
                            {{ form.errors.website }}
                        </p>
                    </div>

                    <!-- Warning balance -->
                    <div>
                        <label for="warning_balance" class="block text-sm font-medium text-gray-700">
                            Warning Balance
                        </label>

                        <input id="warning_balance" v-model="form.warning_balance" type="number" step="0.01"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]" />

                        <p v-if="form.errors.warning_balance" class="mt-1 text-sm text-red-600">
                            {{ form.errors.warning_balance }}
                        </p>
                    </div>

                    <div>
                        <label for="legacy_paidby_id" class="block text-sm font-medium text-gray-700">
                            Legacy PaidBy ID
                        </label>

                        <input id="legacy_paidby_id" v-model="form.legacy_paidby_id" type="number"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-[#477b67] focus:outline-none focus:ring-1 focus:ring-[#477b67]" />

                        <p class="mt-1 text-xs text-gray-500">
                            Original PaidBy ID from the legacy system.
                        </p>

                        <p v-if="form.errors.legacy_paidby_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.legacy_paidby_id }}
                        </p>
                    </div>

                    <!-- Active -->
                    <div class="flex items-center gap-3">
                        <input id="is_active" v-model="form.is_active" type="checkbox"
                            class="h-4 w-4 rounded border-gray-300" />

                        <label for="is_active" class="text-sm font-medium text-gray-700">
                            Active account
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <Link :href="`/households/${household.id}/accounts`"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>

                    <button type="submit" :disabled="form.processing"
                        class="rounded-lg bg-[#477b67] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3d6b59] disabled:opacity-50">
                        {{
                            form.processing
                                ? 'Saving...'
                                : account
                                    ? 'Save Changes'
                                    : 'Create Account'
                        }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>
