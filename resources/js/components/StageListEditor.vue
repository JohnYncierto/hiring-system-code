<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export type Stage = {
    name: string;
    kind: 'interview' | 'non_interview';
    scorecard_template_id: number | null;
    default_duration_minutes: number | null;
    staleness_threshold_days: number;
};

type ScorecardTemplateOption = {
    id: number;
    name: string;
};

defineProps<{
    scorecardTemplates: ScorecardTemplateOption[];
    errors?: Record<string, string>;
}>();

const stages = defineModel<Stage[]>({ required: true });

function addStage() {
    stages.value = [
        ...stages.value,
        {
            name: '',
            kind: 'non_interview',
            scorecard_template_id: null,
            default_duration_minutes: null,
            staleness_threshold_days: 3,
        },
    ];
}

function removeStage(index: number) {
    if (stages.value.length <= 1) {
        return;
    }

    stages.value = stages.value.filter((_, i) => i !== index);
}

function onKindChange(stage: Stage, kind: 'interview' | 'non_interview') {
    stage.kind = kind;

    if (kind === 'non_interview') {
        stage.scorecard_template_id = null;
        stage.default_duration_minutes = null;
    }
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="(stage, index) in stages"
            :key="index"
            class="space-y-3 rounded-md border p-4"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="grid flex-1 gap-2">
                    <Label :for="`stage-${index}-name`">Stage name</Label>
                    <Input
                        :id="`stage-${index}-name`"
                        v-model="stage.name"
                        :name="`stages[${index}][name]`"
                        placeholder="e.g. Onsite interview"
                    />
                    <InputError :message="errors?.[`stages.${index}.name`]" />
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="mt-6"
                    :disabled="stages.length <= 1"
                    @click="removeStage(index)"
                >
                    <Trash2 class="size-4" />
                    <span class="sr-only">Remove stage</span>
                </Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label :for="`stage-${index}-kind`">Kind</Label>
                    <Select
                        :model-value="stage.kind"
                        @update:model-value="(v) => onKindChange(stage, v as 'interview' | 'non_interview')"
                    >
                        <SelectTrigger :id="`stage-${index}-kind`">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="interview">Interview</SelectItem>
                            <SelectItem value="non_interview">Non-interview</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors?.[`stages.${index}.kind`]" />
                </div>

                <div class="grid gap-2">
                    <Label :for="`stage-${index}-staleness`">Staleness threshold (days)</Label>
                    <Input
                        :id="`stage-${index}-staleness`"
                        v-model.number="stage.staleness_threshold_days"
                        type="number"
                        min="1"
                        :name="`stages[${index}][staleness_threshold_days]`"
                    />
                    <InputError :message="errors?.[`stages.${index}.staleness_threshold_days`]" />
                </div>
            </div>

            <div v-if="stage.kind === 'interview'" class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label :for="`stage-${index}-scorecard`">Scorecard template</Label>
                    <Select
                        :model-value="stage.scorecard_template_id ? String(stage.scorecard_template_id) : undefined"
                        @update:model-value="(v) => (stage.scorecard_template_id = v ? Number(v) : null)"
                    >
                        <SelectTrigger :id="`stage-${index}-scorecard`">
                            <SelectValue placeholder="None" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="template in scorecardTemplates"
                                :key="template.id"
                                :value="String(template.id)"
                            >
                                {{ template.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors?.[`stages.${index}.scorecard_template_id`]" />
                </div>

                <div class="grid gap-2">
                    <Label :for="`stage-${index}-duration`">Default duration (minutes)</Label>
                    <Input
                        :id="`stage-${index}-duration`"
                        :model-value="stage.default_duration_minutes ?? ''"
                        @update:model-value="(v) => (stage.default_duration_minutes = v === '' ? null : Number(v))"
                        type="number"
                        min="1"
                        :name="`stages[${index}][default_duration_minutes]`"
                    />
                    <InputError :message="errors?.[`stages.${index}.default_duration_minutes`]" />
                </div>
            </div>
        </div>

        <Button type="button" variant="outline" @click="addStage">
            Add stage
        </Button>
    </div>
</template>