<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import RoleCheckboxGroup from '@/components/RoleCheckboxGroup.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Users', href: UserController.index() },
            { title: 'Create user', href: UserController.create() },
        ] satisfies BreadcrumbItem[],
    },
});

const selectedRoles = ref<string[]>([]);
</script>

<template>
    <Head title="Create user" />

    <div class="flex flex-col space-y-6 p-6">
        <Heading
            title="Create user"
            description="Add a new staff account and assign its roles"
        />

        <Form
            v-bind="UserController.store.form()"
            :transform="(data) => ({ ...data, roles: selectedRoles })"
            class="max-w-xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input id="name" name="name" required autofocus autocomplete="name" placeholder="Full name" />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input id="email" type="email" name="email" required autocomplete="username" placeholder="Email address" />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput id="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters" />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <PasswordInput id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm password" />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="grid gap-2">
                <Label>Roles</Label>
                <RoleCheckboxGroup v-model="selectedRoles" />
                <InputError :message="errors.roles" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="create-user-button">Create user</Button>
                <Button variant="outline" as-child>
                    <Link :href="UserController.index().url">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>