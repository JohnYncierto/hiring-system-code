<script setup lang="ts">
import { ROLE_OPTIONS } from '@/lib/roles'
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

export type RoleOption = {
    value: string;
    label: string;
};



const modelValue = defineModel<string[]>({ required: true });

function toggle(value: string, checked: boolean) {
    if (checked) {
        if (!modelValue.value.includes(value)) {
            modelValue.value = [...modelValue.value, value];
        }
        return;
    }

    modelValue.value = modelValue.value.filter((role) => role !== value);
}
</script>

<template>
    <div class="grid gap-3 sm:grid-cols-2">
        <Label
            v-for="role in ROLE_OPTIONS"
            :key="role.value"
            :for="`role-${role.value}`"
            class="flex items-center gap-2 rounded-md border p-3 font-normal"
        >
            <Checkbox
                :id="`role-${role.value}`"
                :model-value="modelValue.includes(role.value)"
                @update:model-value="(checked) => toggle(role.value, checked === true)"
            />
            {{ role.label }}
        </Label>
    </div>
</template>