<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    FolderGit2,
    LayoutGrid,
    ReceiptText,
    Tags,
    Users,
    WalletCards,
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
    household?: {
        id: number;
    };

    auth?: {
        user?: {
            households?: {
                id: number;
            }[];
        };
    };
};

const page = usePage<PageProps>();

const isAdmin = computed(() => {
    const auth = page.props.auth as {
        user?: {
            role?: string;
        };
    };

    return auth?.user?.role === 'admin';
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

const mainNavItems = computed<NavItem[]>(() => {
    const id = householdId.value;

    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: id
                ? `/households/${id}/dashboard`
                : '#',
            icon: LayoutGrid,
        },
    ];

    if (id) {
        items.push(
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
        );
    }

    return items;
});
const adminNavItems: NavItem[] = [
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs',
        icon: BookOpen,
    },
];
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <div v-if="isAdmin" class="px-2 pb-1 text-xs font-medium text-muted-foreground">
                Administration
            </div>

            <NavFooter v-if="isAdmin" :items="adminNavItems" />

            <NavUser />
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>
