<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectMember, ProjectActivity, User};
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProjectTeamController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('project_team.manage');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|max:100',
            'hours_allocation' => 'nullable|numeric|min:0',
        ]);

        $member = ProjectMember::updateOrCreate(
            [
                'project_id' => $project->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'role' => $validated['role'],
                'hours_allocation' => $validated['hours_allocation'] ?? 0,
                'assigned_date' => now(),
                'status' => 'active',
            ]
        );

        $user = User::find($validated['user_id']);
        ProjectActivity::log($project->id, 'Team member added', "Assigned {$user->name} as {$validated['role']}.");

        if ($user) {
            app(\App\Services\NotificationService::class)->send(
                $user,
                'Assigned to Project',
                "You have been added to project '{$project->name}' as {$validated['role']}.",
                ['category' => 'project', 'url' => route('projects.show', $project), 'color' => 'info']
            );
        }

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => "{$user->name} assigned to project team."]);
    }

    public function destroy(Project $project, ProjectMember $member): RedirectResponse
    {
        $this->authorize('project_team.manage');

        $userName = $member->user?->name ?? 'Team member';
        $role = $member->role;
        $member->delete();

        ProjectActivity::log($project->id, 'Team member removed', "Removed {$userName} ({$role}) from project team.");

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => "{$userName} removed from project team."]);
    }
}
