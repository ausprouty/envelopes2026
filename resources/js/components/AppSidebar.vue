<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Users,
    ChartNoAxesColumn,
    CircleDollarSign,
    LayoutGrid,
    ReceiptText,
    Tags,
    Upload,
    WalletCards,
    ArrowRightLeft,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

type PageProps = {
    adminHouseholds?: {
        id: number;
        household_name: string;
    }[];

    household?: {
        id: number;
    };

    auth?: {
        user?: {
            role?: 'admin' | 'user';
            households?: {
                id: number;
                pivot?: {
                    role?: 'member' | 'coach';
                };
            }[];
        };
    };
};

const page = usePage<PageProps>();

const isAdmin = computed(() => {
    return page.props.auth?.user?.role === 'admin';
});

const adminHouseholds = computed(() => {
    return page.props.adminHouseholds ?? [];
});

const isCoach = computed(() => {
    return page.props.auth?.user?.households?.[0]?.pivot?.role === 'coach';
});

const householdId = computed(
    () =>
        page.props.household?.id ??
        page.props.auth?.user?.households?.[0]?.id,
);
const dashboardUrl = computed(() => {
    const id = householdId.value;

    return id
        ? `/households/${id}/dashboard`
        : '#';
});

const dashboardNavItems = computed<NavItem[]>(() => {
    const id = householdId.value;

    return [
        {
            title: 'Dashboard',
            href: id
                ? `/households/${id}/dashboard`
                : '#',
            icon: LayoutGrid,
        },
    ];
});

const taskNavItems = computed<NavItem[]>(() => {
    const id = householdId.value;

    if (!id) {
        return [];
    }

    return [
        {
            title: 'Allocate Income',
            href: `/households/${id}/income-allocations/create`,
            icon: CircleDollarSign,
        },
        {
            title: 'Assign Transactions',
            href: `/households/${id}/transactions/assign`,
            icon: Tags,
        },
        {
            title: 'Import Transactions',
            href: `/households/${id}/transactions/import`,
            icon: Upload,
        },
        {
            title: 'Move Between Envelopes',
            href: `/households/${id}/category-transfers/create`,
            icon: ArrowRightLeft,
        },
    ];
});

const dataNavItems = computed<NavItem[]>(() => {
    const id = householdId.value;

    if (!id) {
        return [];
    }

    return [
        {
            title: 'Transactions',
            href: `/households/${id}/transactions`,
            icon: ReceiptText,
        },
        {
            title: 'Accounts',
            href: `/households/${id}/accounts`,
            icon: WalletCards,
        },
        {
            title: 'Categories',
            href: `/households/${id}/categories`,
            icon: Tags,
        },
        {
            title: 'Import Profiles',
            href: `/households/${id}/import-profiles`,
            icon: Upload,
        },


    ];
});

const reportNavItems = computed<NavItem[]>(() => {
    const id = householdId.value;

    if (!id) {
        return [];
    }

    return [
        {
            title: 'Reports',
            href: `/households/${id}/reports`,
            icon: ChartNoAxesColumn,
        },
    ];
});
const adminNavItems: NavItem[] = [
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
    },

];

function switchHousehold(event: Event) {
    const target = event.target as HTMLSelectElement;

    if (!target.value) {
        return;
    }

    window.location.href =
        `/households/${target.value}/dashboard`;
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="dashboardNavItems" />

            <template v-if="!isCoach">
                <div class="px-3 pt-3 pb-1 text-xs font-medium text-muted-foreground">
                    Tasks
                </div>

                <NavMain :items="taskNavItems" />

                <div class="px-3 pt-3 pb-1 text-xs font-medium text-muted-foreground">
                    Data
                </div>

                <NavMain :items="dataNavItems" />
            </template>

            <div class="px-3 pt-3 pb-1 text-xs font-medium text-muted-foreground">
                Reports
            </div>

            <NavMain :items="reportNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <template v-if="isAdmin">
                <div class="px-2 pb-1 text-xs font-medium text-muted-foreground">
                    Administration
                </div>

                <div class="px-2 pb-3">
                    <label class="mb-1 block text-xs font-medium text-muted-foreground">
                        Working with
                    </label>

                    <select :value="householdId ?? ''"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                        @change="switchHousehold">
                        <option value="">
                            Select household
                        </option>

                        <option v-for="household in adminHouseholds" :key="household.id" :value="household.id">
                            {{ household.household_name }}
                        </option>
                    </select>
                </div>

                <NavFooter :items="adminNavItems" />
            </template>

            <NavUser />
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>
