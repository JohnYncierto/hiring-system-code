<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import PipelineTemplateController from '@/actions/App/Http/Controllers/Admin/PipelineTemplateController';
import type { BreadcrumbItem } from '@/types';

type PipelineTemplateSummary = {
    id: number;
    name: string;
    is_active: boolean;
    stages_count: number;
};

const props = defineProps<{
    templates: PipelineTemplateSummary[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pipeline Templates', href: PipelineTemplateController.index() },
        ] satisfies BreadcrumbItem[],
    },
});

function destroy(template: PipelineTemplateSummary) {
    if (!confirm(`Delete '${template.name}'? This can't be undone.`)) {
        return;
    }

    router.delete(PipelineTemplateController.destroy(template.id).url);
}
</script>

<template>
    <Head title="Pipeline Templates" />

    <div class="flex flex-col space-y-6 p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Pipeline Templates"
                description="Reusable stage sequences assigned to job openings"
            />
            <Button as-child>
                <Link :href="PipelineTemplateController.create().url">New template</Link>
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Stages</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="template in props.templates" :key="template.id">
                    <TableCell>{{ template.name }}</TableCell>
                    <TableCell>
                        <Badge variant="secondary">{{ template.stages_count }}</Badge>
                    </TableCell>
                    <TableCell>
                        <Badge :variant="template.is_active ? 'default' : 'outline'">
                            {{ template.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </TableCell>
                    <TableCell class="flex justify-end gap-2">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="PipelineTemplateController.edit(template.id).url">
                                Edit
                            </Link>
                        </Button>
                        <Button variant="destructive" size="sm" @click="destroy(template)">
                            Delete
                        </Button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>