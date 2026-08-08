<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type Household = {
    id: number;
    household_name: string;
};

type Category = {
    id: number;
    code: string | null;
    name: string;
    parent_category_id: number | null;
    category_type: string;
    context: string;
    tracks_balance: boolean;
    is_active: boolean;
    display_order: number;
};

type ParentCategory = {
    id: number;
    name: string;
};

const props = defineProps<{
    household: Household;
    category: Category | null;
    parentCategories: ParentCategory[];
}>();

const isEditing = computed(() => props.category !== null);

const form = useForm({
    code: props.category?.code ?? '',
    name: props.category?.name ?? '',
    parent_category_id: props.category?.parent_category_id ?? null,
    category_type: props.category?.category_type ?? 'expense',
    context: props.category?.context ?? 'household',
    tracks_balance: props.category?.tracks_balance ?? true,
    is_active: props.category?.is_active ?? true,
    display_order: props.category?.display_order ?? 0,
});

const submit = () => {
    if (isEditing.value && props.category) {
        form.put(
            `/households/${props.household.id}/categories/${props.category.id}`,
        );
    } else {
        form.post(`/households/${props.household.id}/categories`);
    }
};
</script>

<template>

    <Head :title="isEditing ? 'Edit Category' : 'Add Category'" />

    <div class="mx-auto max-w-3xl p-6">
        <div class="mb-6">
            <Link :href="`/households/${household.id}/categories`"
                class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                ← Back to Categories
            </Link>

            <h1 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-white">
                {{ isEditing ? 'Edit Category' : 'Add Category' }}
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ household.household_name }}
            </p>
        </div>

        <form class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900"
            @submit.prevent="submit">
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Category Name
                    </label>

                    <input id="name" v-model="form.name" type="text" autofocus
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Code / Display Order -->
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Code
                        </label>

                        <input id="code" v-model="form.code" type="text"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div>
                        <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Display Order
                        </label>

                        <input id="display_order" v-model.number="form.display_order" type="number"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                        <p v-if="form.errors.display_order" class="mt-1 text-sm text-red-600">
                            {{ form.errors.display_order }}
                        </p>
                    </div>
                </div>

                <!-- Type / Parent -->
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="category_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Type
                        </label>

                        <select id="category_type" v-model="form.category_type"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                            <option value="asset">Asset</option>
                            <option value="transfer">Transfer</option>
                            <option value="reimbursement">
                                Reimbursement
                            </option>
                            <option value="heading">Heading</option>
                        </select>

                        <p v-if="form.errors.category_type" class="mt-1 text-sm text-red-600">
                            {{ form.errors.category_type }}
                        </p>
                    </div>

                    <div>
                        <label for="parent_category_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Parent Heading
                        </label>

                        <select id="parent_category_id" v-model="form.parent_category_id"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option :value="null">No parent</option>

                            <option v-for="parent in parentCategories" :key="parent.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </select>

                        <p v-if="form.errors.parent_category_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.parent_category_id }}
                        </p>
                    </div>
                </div>

                <!-- Context -->
                <div>
                    <label for="context" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Context
                    </label>

                    <select id="context" v-model="form.context"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="household">Household</option>
                        <option value="ministry_au">Ministry AU</option>
                        <option value="ministry_us">Ministry US</option>
                        <option value="other">Other</option>
                    </select>

                    <p v-if="form.errors.context" class="mt-1 text-sm text-red-600">
                        {{ form.errors.context }}
                    </p>
                </div>

                <!-- Switches -->
                <div class="space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                    <label class="flex items-start gap-3">
                        <input v-model="form.tracks_balance" type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-gray-300" />

                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                Track balance
                            </span>

                            <span class="block text-sm text-gray-500 dark:text-gray-400">
                                Include this category when calculating envelope
                                balances.
                            </span>
                        </span>
                    </label>

                    <label class="flex items-start gap-3">
                        <input v-model="form.is_active" type="checkbox" class="mt-1 h-4 w-4 rounded border-gray-300" />

                        <span>
                            <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                Active
                            </span>

                            <span class="block text-sm text-gray-500 dark:text-gray-400">
                                Inactive categories remain in the database but
                                are not normally used for new transactions.
                            </span>
                        </span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <Link :href="`/households/${household.id}/categories`"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                        Cancel
                    </Link>

                    <button type="submit" :disabled="form.processing"
                        class="rounded-lg bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:bg-[#3c6958] disabled:cursor-not-allowed disabled:opacity-50">
                        {{
                            form.processing
                                ? 'Saving...'
                                : isEditing
                                    ? 'Save Changes'
                                    : 'Add Category'
                        }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
