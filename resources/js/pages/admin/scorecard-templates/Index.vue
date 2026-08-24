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
import ScorecardTemplateController from '@/actions/App/Http/Controllers/Admin/ScorecardTemplateController';
import type { BreadcrumbItem } from '@/types';

type ScorecardTemplateSummary = {
    id: number;
    name: string;
    criteria_count: number;
};

const props = defineProps<{
    templates: ScorecardTemplateSummary[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Scorecard Templates', href: ScorecardTemplateController.index() },
        ] satisfies BreadcrumbItem[],
    },
});

function destroy(template: ScorecardTemplateSummary) {
    if (!confirm(`Delete '${template.name}'? This can't be undone.`)) {
        return;
    }

    router.delete(ScorecardTemplateController.destroy(template.id).url);
}
</script>

<template>
    <Head title="Scorecard Templates" />

    <div class="flex flex-col space-y-6 p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Scorecard Templates"
                description="Reusable criteria sets used by interview stages"
            />
            <Button as-child>
                <Link :href="ScorecardTemplateController.create().url">New template</Link>
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Criteria</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="template in props.templates" :key="template.id">
                    <TableCell>{{ template.name }}</TableCell>
                    <TableCell>
                        <Badge variant="secondary">{{ template.criteria_count }}</Badge>
                    </TableCell>
                    <TableCell class="flex justify-end gap-2">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="ScorecardTemplateController.edit(template.id).url">
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