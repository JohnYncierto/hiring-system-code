<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import RoleCheckboxGroup from '@/components/RoleCheckboxGroup.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    user: AdminUser;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: UserController.index() },
            { title: 'Edit user', href: UserController.edit(props.user.id) },
        ] satisfies BreadcrumbItem[],
    },
});

const selectedRoles = ref<string[]>(props.user.roles.map((assignment) => assignment.role));
</script>

<template>
    <Head title="Edit user" />

    <div class="flex flex-col space-y-6 p-6">
        <Heading
            title="Edit user"
            :description="`Update ${user.name}'s details and roles`"
        />

        <Form
            v-bind="UserController.update.form(user.id)"
            :transform="(data) => ({ ...data, roles: selectedRoles })"
            class="max-w-xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" required autofocus autocomplete="name" :default-value="user.name" placeholder="Full name" />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input id="email" type="email" name="email" required autocomplete="username" :default-value="user.email" placeholder="Email address" />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label>Roles</Label>
                <RoleCheckboxGroup v-model="selectedRoles" />
                <InputError :message="errors.roles" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-user-button">Save changes</Button>
                <Button variant="outline" as-child>
                    <Link :href="UserController.index().url">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>