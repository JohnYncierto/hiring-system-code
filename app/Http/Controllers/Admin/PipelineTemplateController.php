<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PipelineTemplate;
use App\Models\ScorecardTemplate;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PipelineTemplateController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', PipelineTemplate::class);

        return Inertia::render('admin/pipeline-templates/Index', [
            'templates' => PipelineTemplate::withCount('stages')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PipelineTemplate::class);

        return Inertia::render('admin/pipeline-templates/Create', [
            'scorecardTemplates' => ScorecardTemplate::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('create', PipelineTemplate::class);

        $validated = $this->validateTemplate($request);

        $template = PipelineTemplate::create([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'],
        ]);

        $this->syncStages($template, $validated['stages']);

        $activityLogger->log(action: 'pipeline_template.created', subject: $template);

        return redirect()->route('admin.pipeline-templates.index');
    }

    public function edit(PipelineTemplate $pipelineTemplate): Response
    {
        $this->authorize('update', $pipelineTemplate);

        return Inertia::render('admin/pipeline-templates/Edit', [
            'template' => $pipelineTemplate->load('stages'),
            'scorecardTemplates' => ScorecardTemplate::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, PipelineTemplate $pipelineTemplate, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('update', $pipelineTemplate);

        $validated = $this->validateTemplate($request);

        $pipelineTemplate->update([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'],
        ]);

        $this->syncStages($pipelineTemplate, $validated['stages']);

        $activityLogger->log(action: 'pipeline_template.updated', subject: $pipelineTemplate);

        return redirect()->route('admin.pipeline-templates.index');
    }

    public function destroy(PipelineTemplate $pipelineTemplate, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('delete', $pipelineTemplate);

        $pipelineTemplate->delete();

        $activityLogger->log(action: 'pipeline_template.deleted', subject: $pipelineTemplate);

        return redirect()->route('admin.pipeline-templates.index');
    }

    /**
     * @return array{name: string, is_active: bool, stages: array<int, array{name: string, kind: string, scorecard_template_id: int|null, default_duration_minutes: int|null, staleness_threshold_days: int}>}
     */
    protected function validateTemplate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.kind' => ['required', 'in:interview,non_interview'],
            'stages.*.scorecard_template_id' => ['nullable', 'exists:scorecard_templates,id'],
            'stages.*.default_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'stages.*.staleness_threshold_days' => ['required', 'integer', 'min:1'],
        ]);
    }

    /**
     * @param  array<int, array{name: string, kind: string, scorecard_template_id?: int|null, default_duration_minutes?: int|null, staleness_threshold_days: int}>  $stages
     */
    protected function syncStages(PipelineTemplate $template, array $stages): void
    {
        $template->stages()->delete();

        foreach ($stages as $position => $stage) {
            $template->stages()->create([
                'name' => $stage['name'],
                'position' => $position,
                'kind' => $stage['kind'],
                'scorecard_template_id' => $stage['scorecard_template_id'] ?? null,
                'default_duration_minutes' => $stage['default_duration_minutes'] ?? null,
                'staleness_threshold_days' => $stage['staleness_threshold_days'],
            ]);
        }
    }
}
