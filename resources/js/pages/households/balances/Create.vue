<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { route } from '@/lib/route';

interface Household {
    id: number;
    household_name: string;
}

interface FinancialAccount {
    id: number;
    account_name: string;
    institution_name: string | null;
    account_type: string;
    category_type: string;
    currency: string;
}

        const props = defineProps<{
            household: Household;
            accounts: FinancialAccount[];
            selectedAccountId: number | null;
        }>();

const today = new Date().toISOString().slice(0, 10);


const form = useForm({
    financial_account_id: props.selectedAccountId ?? '',
    balance_date: today,
    ledger_balance: '',
});

function submit() {
    form.post(
        route('households.balances.store', {
            household: props.household.id,
        }),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.financial_account_id = '';
                form.ledger_balance = '';
            },
        },
    );
}
</script>

<template>

    <Head title="Record Balance" />

    <div class="mx-auto max-w-3xl p-6">
        <h1 class="mb-6 text-2xl font-semibold">
            Record Balance
        </h1>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="mb-2 block font-medium">
                    Account
                </label>

                <select v-model="form.financial_account_id" class="w-full rounded border p-3">
                    <option value="">
                        Select an account
                    </option>

                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                        {{
                            account.institution_name
                                ? `${account.institution_name} — ${account.account_name}`
                                : account.account_name
                        }}
                    </option>
                </select>

                <div v-if="form.errors.financial_account_id" class="mt-1 text-sm text-red-600">
                    {{ form.errors.financial_account_id }}
                </div>
            </div>

            <div>
                <label class="mb-2 block font-medium">
                    Date
                </label>

                <input v-model="form.balance_date" type="date" class="w-full rounded border p-3" />

                <div v-if="form.errors.balance_date" class="mt-1 text-sm text-red-600">
                    {{ form.errors.balance_date }}
                </div>
            </div>

            <div>
                <label class="mb-2 block font-medium">
                    Balance
                </label>

                <input v-model="form.ledger_balance" type="number" step="0.01" class="w-full rounded border p-3" />

                <div v-if="form.errors.ledger_balance" class="mt-1 text-sm text-red-600">
                    {{ form.errors.ledger_balance }}
                </div>
            </div>

            <button type="submit" :disabled="form.processing"
                class="rounded bg-blue-600 px-5 py-3 text-white disabled:opacity-50">
                Record Balance
            </button>
        </form>
    </div>
</template>
