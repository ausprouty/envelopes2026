<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';

type Household = {
    id: number;
    household_name: string;
};

type User = {
    id: number;
    name: string;
    email: string;
    households: Array<
        Household & {
            pivot?: {
                role?: 'member' | 'coach';
            };
        }
    >;
};

const props = defineProps<{
    users: User[];
    households: Household[];
}>();

const updateHousehold = (
    userId: number,
    householdId: number,
    householdRole: 'member' | 'coach'
) => {
    router.put(`/admin/users/${userId}/household`, {
        household_id: householdId,
        household_role: householdRole,
    });
};

const deleteUser = (user: User) => {
    if (!confirm(`Delete ${user.name}? This cannot be undone.`)) {
        return;
    }

    router.delete(`/admin/users/${user.id}`);
};
</script>

<template>
    <div class="mx-auto max-w-5xl p-6">
        <!-- Page heading -->
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-[#18332b]">
                    User Administration
                </h1>

                <p class="mt-2 text-[#63736d]">
                    Assign each user to the correct household.
                </p>
            </div>

            <Link href="/admin/users/create"
                class="rounded-lg bg-[#477b67] px-4 py-2 text-sm font-medium text-white hover:bg-[#3c6958]">
                + Add User
            </Link>
        </div>

        <!-- Users table -->
        <div class="overflow-hidden rounded-2xl border border-[#cbd5d1] bg-white shadow-sm">
            <table class="w-full">
                <thead class="bg-[#f4f7f5]">
                    <tr>
                        <th class="px-5 py-4 text-left text-sm font-semibold text-[#18332b]">
                            User
                        </th>

                        <th class="px-5 py-4 text-left text-sm font-semibold text-[#18332b]">
                            Email
                        </th>

                        <th class="px-5 py-4 text-left text-sm font-semibold text-[#18332b]">
                            Household
                        </th>

                        <th class="px-5 py-4 text-left text-sm font-semibold text-[#18332b]">
                            Access
                        </th>

                        <th class="px-5 py-4 text-left text-sm font-semibold text-[#18332b]">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#d6dedb]">
                    <tr v-for="user in props.users" :key="user.id">
                        <!-- User -->
                        <td class="px-5 py-4 font-medium text-[#18332b]">
                            {{ user.name }}
                        </td>

                        <!-- Email -->
                        <td class="px-5 py-4 text-[#63736d]">
                            {{ user.email }}
                        </td>

                        <!-- Household -->
                        <td class="px-5 py-4">
                            <select
                                class="w-full max-w-xs rounded-lg border border-[#aebbb5] bg-white px-3 py-2 text-[#18332b]"
                                :value="user.households[0]?.id ?? ''" @change="
                                    updateHousehold(
                                        user.id,
                                        Number(($event.target as HTMLSelectElement).value),
                                        user.households[0]?.pivot?.role ?? 'member'
                                    )
                                    ">
                                <option value="" disabled>
                                    Select household
                                </option>

                                <option v-for="household in props.households" :key="household.id" :value="household.id">
                                    {{ household.household_name }}
                                </option>
                            </select>
                        </td>
                        <!-- Access -->

                        <td class="px-5 py-4">
                            <select class="rounded-lg border border-[#aebbb5] bg-white px-3 py-2 text-[#18332b]"
                                :value="user.households[0]?.pivot?.role ?? 'member'" @change="
                                    updateHousehold(
                                        user.id,
                                        user.households[0]?.id ?? 0,
                                        ($event.target as HTMLSelectElement).value as 'member' | 'coach'
                                    )
                                    ">
                                <option value="member">Member</option>
                                <option value="coach">Coach</option>
                            </select>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <Link :href="`/admin/users/${user.id}/edit`"
                                    class="font-medium text-[#477b67] hover:underline">
                                    Edit
                                </Link>

                                <button type="button" class="font-medium text-red-600 hover:underline"
                                    @click="deleteUser(user)">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
