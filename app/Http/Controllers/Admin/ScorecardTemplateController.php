<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScorecardTemplate;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScorecardTemplateController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', ScorecardTemplate::class);

        return Inertia::render('admin/scorecard-templates/Index', [
            'templates' => ScorecardTemplate::withCount('criteria')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ScorecardTemplate::class);

        return Inertia::render('admin/scorecard-templates/Create');
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('create', ScorecardTemplate::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.name' => ['required', 'string', 'max:255'],
            'criteria.*.description' => ['nullable', 'string'],
        ]);

        $template = ScorecardTemplate::create(['name' => $validated['name']]);

        foreach ($validated['criteria'] as $position => $criterion) {
            $template->criteria()->create([
                'name' => $criterion['name'],
                'description' => $criterion['description'] ?? null,
                'position' => $position,
            ]);
        }

        $activityLogger->log(action: 'scorecard_template.created', subject: $template);

        return redirect()->route('admin.scorecard-templates.index');
    }

    public function edit(ScorecardTemplate $scorecardTemplate): Response
    {
        $this->authorize('update', $scorecardTemplate);

        return Inertia::render('admin/scorecard-templates/Edit', [
            'template' => $scorecardTemplate->load('criteria'),
        ]);
    }

    public function update(Request $request, ScorecardTemplate $scorecardTemplate, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('update', $scorecardTemplate);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.name' => ['required', 'string', 'max:255'],
            'criteria.*.description' => ['nullable', 'string'],
        ]);

        $scorecardTemplate->update(['name' => $validated['name']]);

        $scorecardTemplate->criteria()->delete();
        foreach ($validated['criteria'] as $position => $criterion) {
            $scorecardTemplate->criteria()->create([
                'name' => $criterion['name'],
                'description' => $criterion['description'] ?? null,
                'position' => $position,
            ]);
        }

        $activityLogger->log(action: 'scorecard_template.updated', subject: $scorecardTemplate);

        return redirect()->route('admin.scorecard-templates.index');
    }

    public function destroy(ScorecardTemplate $scorecardTemplate, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->authorize('delete', $scorecardTemplate);

        $scorecardTemplate->delete();

        $activityLogger->log(action: 'scorecard_template.deleted', subject: $scorecardTemplate);

        return redirect()->route('admin.scorecard-templates.index');
    }
}
