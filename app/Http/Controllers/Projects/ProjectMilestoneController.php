<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectMilestoneRequest;
use App\Models\{Project, ProjectMilestone, ProjectActivity};
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProjectMilestoneController extends Controller
{
    public function store(ProjectMilestoneRequest $request): RedirectResponse
    {
        $this->authorize('milestones.create');

        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        $milestone = ProjectMilestone::create($validated);

        ProjectActivity::log($milestone->project_id, 'Milestone created', "Milestone '{$milestone->name}' created.");

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Milestone created successfully.']);
    }

    public function update(ProjectMilestoneRequest $request, ProjectMilestone $milestone): RedirectResponse
    {
        $this->authorize('milestones.edit');

        $validated = $request->validated();
        $oldStatus = $milestone->status;

        $milestone->update($validated);

        if ($oldStatus !== $milestone->status) {
            ProjectActivity::log($milestone->project_id, 'Milestone updated', "Milestone '{$milestone->name}' status changed to '{$milestone->status}'.");
        }

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Milestone updated successfully.']);
    }

    public function destroy(ProjectMilestone $milestone): RedirectResponse
    {
        $this->authorize('milestones.delete');

        $projectId = $milestone->project_id;
        $name = $milestone->name;
        $milestone->delete();

        ProjectActivity::log($projectId, 'Milestone deleted', "Milestone '{$name}' deleted.");

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Milestone deleted.']);
    }
}
