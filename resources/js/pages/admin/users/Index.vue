<script setup lang="ts">
import { router } from '@inertiajs/vue3';

type Household = {
    id: number;
    household_name: string;
};

type User = {
    id: number;
    name: string;
    email: string;
    households: Household[];
};

const props = defineProps<{
    users: User[];
    households: Household[];
}>();

const updateHousehold = (userId: number, householdId: number) => {
    router.put(`/admin/users/${userId}/household`, {
        household_id: householdId,
    });
};
</script>

<template>
    <div class="mx-auto max-w-5xl p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#18332b]">
                User Administration
            </h1>

            <p class="mt-2 text-[#63736d]">
                Assign each user to the correct household.
            </p>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-[#dde4df] bg-white shadow-sm"
        >
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
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#e7ece9]">
                    <tr
                        v-for="user in props.users"
                        :key="user.id"
                    >
                        <td class="px-5 py-4 font-medium text-[#18332b]">
                            {{ user.name }}
                        </td>

                        <td class="px-5 py-4 text-[#63736d]">
                            {{ user.email }}
                        </td>

                        <td class="px-5 py-4">
                            <select
                                class="w-full max-w-xs rounded-lg border border-[#cfd8d3]
                                       bg-white px-3 py-2 text-[#18332b]"
                                :value="user.households[0]?.id ?? ''"
                                @change="
                                    updateHousehold(
                                        user.id,
                                        Number(($event.target as HTMLSelectElement).value)
                                    )
                                "
                            >
                                <option value="" disabled>
                                    Select household
                                </option>

                                <option
                                    v-for="household in props.households"
                                    :key="household.id"
                                    :value="household.id"
                                >
                                    {{ household.household_name }}
                                </option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
