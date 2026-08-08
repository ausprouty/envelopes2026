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
import { dashboard } from '@/routes';
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


const mainNavItems = computed<NavItem[]>(() => {
    const householdId =
        page.props.household?.id ??
        page.props.auth?.user?.households?.[0]?.id;

    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (householdId) {
        items.push(
            {
                title: 'Accounts',
                href: `/households/${householdId}/accounts`,
                icon: WalletCards,
            },
            {
                title: 'Transactions',
                href: `/households/${householdId}/transactions`,
                icon: ReceiptText,
            },
            {
                title: 'Categories',
                href: `/households/${householdId}/categories`,
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
                        <Link :href="dashboard()">
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
