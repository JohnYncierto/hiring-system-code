<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CriterionListEditor, { type Criterion } from '@/components/CriterionListEditor.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ScorecardTemplateController from '@/actions/App/Http/Controllers/Admin/ScorecardTemplateController';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Scorecard Templates', href: ScorecardTemplateController.index() },
            { title: 'New template', href: ScorecardTemplateController.create() },
        ] satisfies BreadcrumbItem[],
    },
});

const criteria = ref<Criterion[]>([{ name: '', description: '' }]);
</script>

<template>
    <Head title="New Scorecard Template" />

    <div class="flex flex-col space-y-6 p-6">
        <Heading
            title="New scorecard template"
            description="Define the criteria interviewers will rate candidates on"
        />

        <Form
            v-bind="ScorecardTemplateController.store.form()"
            :transform="(data) => ({ ...data, criteria })"
            class="max-w-2xl space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Template name</Label>
                <Input id="name" name="name" required autofocus placeholder="e.g. Technical Screen" />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label>Criteria</Label>
                <CriterionListEditor v-model="criteria" :errors="errors" />
                <InputError :message="errors.criteria" />
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="create-scorecard-template-button">
                    Create template
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="ScorecardTemplateController.index().url">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>