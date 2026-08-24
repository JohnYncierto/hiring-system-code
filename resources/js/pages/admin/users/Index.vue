<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import type { BreadcrumbItem } from '@/types';

type RoleAssignment = {
    id: number;
    role: string;
};

type AdminUser = {
    id: number;
    name: string;
    email: string;
    deactivated_at: string | null;
    roles: RoleAssignment[];
};

const props = defineProps<{
    users: AdminUser[];
}>();

const page = usePage();
const currentUserId = computed(() => page.props.auth.user?.id);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: UserController.index() },
        ] satisfies BreadcrumbItem[],
    },
});

function deactivate(user: AdminUser) {
    if (!confirm(`Deactivate ${user.name}? This can be reversed later.`)) {
        return;
    }

    router.patch(UserController.deactivate(user.id).url);
}
</script>

<template>
    <Head title="Users" />
    <div class="flex flex-col space-y-6 p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Users"
                description="Staff accounts and their roles"
            />
            <Button as-child>
                <Link :href="UserController.create().url">Add user</Link>
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Roles</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="user in props.users" :key="user.id">
                    <TableCell>{{ user.name }}</TableCell>
                    <TableCell>{{ user.email }}</TableCell>
                    <TableCell>
                        <div class="flex flex-wrap gap-1">
                            <Badge
                                v-for="assignment in user.roles"
                                :key="assignment.id"
                                variant="secondary"
                            >
                                {{ assignment.role }}
                            </Badge>
                        </div>
                    </TableCell>
                    <TableCell>
                        <Badge
                            :variant="
                                user.deactivated_at
                                    ? 'destructive'
                                    : 'default'
                            "
                        >
                            {{
                                user.deactivated_at
                                    ? 'Deactivated'
                                    : 'Active'
                            }}
                        </Badge>
                    </TableCell>
                    <TableCell class="flex justify-end gap-2">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="UserController.edit(user.id).url">
                                Edit
                            </Link>
                        </Button>
                        <Button
                            v-if="
                                !user.deactivated_at &&
                                user.id !== currentUserId
                            "
                            variant="destructive"
                            size="sm"
                            @click="deactivate(user)"
                        >
                            Deactivate
                        </Button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>