<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import StageListEditor, { type Stage } from '@/components/StageListEditor.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PipelineTemplateController from '@/actions/App/Http/Controllers/Admin/PipelineTemplateController';
import type { BreadcrumbItem } from '@/types';

type ScorecardTemplateOption = {
    id: number;
    name: string;
};

const props = defineProps<{
    scorecardTemplates: ScorecardTemplateOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pipeline Templates', href: PipelineTemplateController.index() },
            { title: 'New template', href: PipelineTemplateController.create() },
        ] satisfies BreadcrumbItem[],
    },
});

const isActive = ref(true);
const stages = ref<Stage[]>([
    {
        name: '',
        kind: 'non_interview',
        scorecard_template_id: null,
        default_duration_minutes: null,
        staleness_threshold_days: 3,
    },
]);
</script>

<template>
    <Head title="New Pipeline Template" />

    <div class="flex flex-col space-y-6 p-6">
        <Heading
            title="New pipeline template"
            description="Define the ordered stages an application will move through"
        />

        <Form
            v-bind="PipelineTemplateController.store.form()"
            :transform="(data) => ({ ...data, is_active: isActive, stages })"
            class="max-w-3xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Template name</Label>
                <Input id="name" name="name" required autofocus placeholder="e.g. Engineering Pipeline" />
                <InputError :message="errors.name" />
            </div>

            <Label class="flex w-fit items-center gap-2 font-normal">
                <Checkbox :model-value="isActive" @update:model-value="(v) => (isActive = v === true)" />
                Active
            </Label>

            <div class="grid gap-2">
                <Label>Stages</Label>
                <StageListEditor
                    v-model="stages"
                    :scorecard-templates="props.scorecardTemplates"
                    :errors="errors"
                />
                <InputError :message="errors.stages" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="create-pipeline-template-button">
                    Create template
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="PipelineTemplateController.index().url">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>