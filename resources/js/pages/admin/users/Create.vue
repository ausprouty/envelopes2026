<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface Household {
    id: number;
    household_name: string;
}

const props = defineProps<{
    households: Household[];
}>();

const form = useForm({
    email: '',
    household_id: '',
    household_role: 'member',
    name: '',
    system_role: 'user',
});

watch(
    () => form.system_role,
    (systemRole) => {
        if (systemRole === 'admin') {
            form.household_id = '';
            form.household_role = 'member';
        }
    }
);

const submit = () => {
    form.post('/admin/users');
};
</script>

<template>

    <Head title="Add User" />

    <div class="p-4 sm:p-6">
        <div class="mx-auto max-w-2xl">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold text-slate-900">
                        Add User
                    </h1>

                    <p class="mt-1 text-slate-600">
                        Create a user and set their access.
                    </p>
                </div>

                <Link href="/admin/users" class="text-sm font-medium text-[#477b67] hover:underline">
                    ← User Administration
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-5 rounded-2xl border border-slate-300 bg-white p-6 shadow-sm">
                <!-- Name -->
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
                        Name
                    </label>

                    <input id="name" v-model="form.name" type="text"
                        class="w-full rounded-lg border border-slate-400 px-3 py-2 focus:border-[#477b67] focus:outline-none focus:ring-2 focus:ring-[#477b67]/20" />

                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">
                        Email
                    </label>

                    <input id="email" v-model="form.email" type="email"
                        class="w-full rounded-lg border border-slate-400 px-3 py-2 focus:border-[#477b67] focus:outline-none focus:ring-2 focus:ring-[#477b67]/20" />

                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- System Role -->
                <div>
                    <label for="system_role" class="mb-1 block text-sm font-medium text-slate-700">
                        System Role
                    </label>

                    <select id="system_role" v-model="form.system_role"
                        class="w-full rounded-lg border border-slate-400 px-3 py-2 focus:border-[#477b67] focus:outline-none focus:ring-2 focus:ring-[#477b67]/20">
                        <option value="user">
                            User
                        </option>

                        <option value="admin">
                            Administrator
                        </option>
                    </select>

                    <div v-if="form.errors.system_role" class="mt-1 text-sm text-red-600">
                        {{ form.errors.system_role }}
                    </div>
                </div>

                <!-- Household -->
                <div v-if="form.system_role === 'user'">
                    <label for="household_id" class="mb-1 block text-sm font-medium text-slate-700">
                        Household
                    </label>

                    <select id="household_id" v-model="form.household_id"
                        class="w-full rounded-lg border border-slate-400 px-3 py-2 focus:border-[#477b67] focus:outline-none focus:ring-2 focus:ring-[#477b67]/20">
                        <option value="" disabled>
                            Select a household
                        </option>

                        <option v-for="household in props.households" :key="household.id" :value="household.id">
                            {{ household.household_name }}
                        </option>
                    </select>

                    <div v-if="form.errors.household_id" class="mt-1 text-sm text-red-600">
                        {{ form.errors.household_id }}
                    </div>
                </div>

                <!-- Household Role -->
                <div v-if="form.system_role === 'user'">
                    <label for="household_role" class="mb-1 block text-sm font-medium text-slate-700">
                        Household Role
                    </label>

                    <select id="household_role" v-model="form.household_role"
                        class="w-full rounded-lg border border-slate-400 px-3 py-2 focus:border-[#477b67] focus:outline-none focus:ring-2 focus:ring-[#477b67]/20">
                        <option value="member">
                            Member
                        </option>

                        <option value="coach">
                            Coach
                        </option>
                    </select>

                    <div v-if="form.errors.household_role" class="mt-1 text-sm text-red-600">
                        {{ form.errors.household_role }}
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 pt-2">
                    <Link href="/admin/users"
                        class="rounded-lg border border-slate-400 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancel
                    </Link>

                    <button type="submit" :disabled="form.processing"
                        class="rounded-lg bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:bg-[#3c6958] disabled:opacity-50">
                        {{ form.processing ? 'Creating...' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
