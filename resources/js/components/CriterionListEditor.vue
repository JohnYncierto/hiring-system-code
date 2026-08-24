<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type Criterion = {
    name: string;
    description: string;
};

defineProps<{
    errors?: Record<string, string>;
}>();

const criteria = defineModel<Criterion[]>({ required: true });

function addCriterion() {
    criteria.value = [...criteria.value, { name: '', description: '' }];
}

function removeCriterion(index: number) {
    if (criteria.value.length <= 1) {
        return;
    }

    criteria.value = criteria.value.filter((_, i) => i !== index);
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="(criterion, index) in criteria"
            :key="index"
            class="space-y-3 rounded-md border p-4"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="grid flex-1 gap-2">
                    <Label :for="`criterion-${index}-name`">Criterion name</Label>
                    <Input
                        :id="`criterion-${index}-name`"
                        v-model="criterion.name"
                        :name="`criteria[${index}][name]`"
                        placeholder="e.g. Communication"
                    />
                    <InputError :message="errors?.[`criteria.${index}.name`]" />
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="mt-6"
                    :disabled="criteria.length <= 1"
                    @click="removeCriterion(index)"
                >
                    <Trash2 class="size-4" />
                    <span class="sr-only">Remove criterion</span>
                </Button>
            </div>

            <div class="grid gap-2">
                <Label :for="`criterion-${index}-description`">Description (optional)</Label>
                <textarea
                    :id="`criterion-${index}-description`"
                    v-model="criterion.description"
                    :name="`criteria[${index}][description]`"
                    rows="2"
                    class="border-input dark:bg-input/30 flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                    placeholder="What the interviewer should assess"
                />
                <InputError :message="errors?.[`criteria.${index}.description`]" />
            </div>
        </div>

        <Button type="button" variant="outline" @click="addCriterion">
            Add criterion
        </Button>
    </div>
</template>