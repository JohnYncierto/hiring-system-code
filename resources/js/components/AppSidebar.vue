<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, FolderGit2, LayoutGrid, Users, ClipboardList, GitBranch } from '@lucide/vue';
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
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import ScorecardTemplateController from '@/actions/App/Http/Controllers/Admin/ScorecardTemplateController';
import PipelineTemplateController from '@/actions/App/Http/Controllers/Admin/PipelineTemplateController';
import type { NavItem } from '@/types';

const page = usePage();
const isAdmin = computed(() =>
    (page.props.auth.user?.roles ?? []).includes('admin'),
);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (isAdmin.value) {
        items.push(
            {
                title: 'Users',
                href: UserController.index(),
                icon: Users,
            },
            {
                title: 'Scorecard Templates',
                href: ScorecardTemplateController.index(),
                icon: ClipboardList,
            },
            {
                title: 'Pipeline Templates',
                href: PipelineTemplateController.index(),
                icon: GitBranch,
            },
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [

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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>