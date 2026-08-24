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

type PipelineStage = {
    id: number;
    name: string;
    kind: 'interview' | 'non_interview';
    position: number;
    scorecard_template_id: number | null;
    default_duration_minutes: number | null;
    staleness_threshold_days: number;
};

type PipelineTemplate = {
    id: number;
    name: string;
    is_active: boolean;
    stages: PipelineStage[];
};

const props = defineProps<{
    template: PipelineTemplate;
    scorecardTemplates: ScorecardTemplateOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pipeline Templates', href: PipelineTemplateController.index() },
            { title: 'Edit template', href: PipelineTemplateController.edit(props.template.id) },
        ] satisfies BreadcrumbItem[],
    },
});

const isActive = ref(props.template.is_active);
const stages = ref<Stage[]>(
    props.template.stages
        .slice()
        .sort((a, b) => a.position - b.position)
        .map((s) => ({
            name: s.name,
            kind: s.kind,
            scorecard_template_id: s.scorecard_template_id,
            default_duration_minutes: s.default_duration_minutes,
            staleness_threshold_days: s.staleness_threshold_days,
        })),
);
</script>

<template>
    <Head title="Edit Pipeline Template" />

    <div class="flex flex-col space-y-6 p-6">
        <Heading
            title="Edit pipeline template"
            :description="`Update stages for ${template.name}`"
        />

        <Form
            v-bind="PipelineTemplateController.update.form(template.id)"
            :transform="(data) => ({ ...data, is_active: isActive, stages })"
            class="max-w-3xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Template name</Label>
                <Input id="name" name="name" required autofocus :default-value="template.name" />
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
                <Button :disabled="processing" data-test="update-pipeline-template-button">
                    Save changes
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="PipelineTemplateController.index().url">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>